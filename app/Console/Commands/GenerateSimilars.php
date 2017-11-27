<?php

namespace App\Console\Commands;

use App\Content;
use Illuminate\Console\Command;

class GenerateSimilars extends Command
{
    protected $signature = 'generate:similars';

    public function handle()
    {
        $maxCount = 100;
        $chunkSize = 10;
        $chunkCount = $maxCount / $chunkSize;

        $count = 0;

        $progress = $this->output->createProgressBar($maxCount);

        $this->info("\nGenerating similar content\n");

        Content::orderBy('updated_at', 'desc')->chunk($chunkSize, function ($contentChunk) use (&$count, $chunkCount, $progress) {
            $contentChunk->each(function ($content) use ($progress) {
                $this->generateSimilars($content);
                $progress->advance();
            });
            $count++;
            if ($count >= $chunkCount) {
                return false;
            }
        });

        $this->info("\n\nDone\n");
    }

    protected function generateSimilars($content)
    {
        $similars = collect();

        collect(['forum', 'news', 'flight'])
            ->each(function ($type) use (&$similars, $content) {
                $similars->put($type, $this->generateSimilarsOfType($content, $type));
            });

        $content['meta->similars'] = $similars;
        $content->timestamps = false;
        $content->save();
    }

    protected function generateSimilarsOfType($sourceContent, $type)
    {
        $similars = collect();

        $maxCount = 100;
        $chunkSize = 10;
        $chunkCount = $maxCount / $chunkSize;

        $count = 0;

        Content::orderBy('updated_at', 'desc')->whereNotIn('id', [$sourceContent->id])
            ->whereType($type)
            ->chunk(100, function ($targetContentChunk) use ($sourceContent, &$similars, &$count, $chunkCount) {
                $targetContentChunk->each(
                    function ($targetContent) use ($sourceContent, &$similars) {
                        $similar = $this->getSimilar($sourceContent, $targetContent);
                        if ($similar) {
                            $similars->push(collect()
                                ->put('items', $similar)
                                ->put('score', $this->getSimilarScore($similar))
                            );
                        }
                    }
                );

                $similars = $similars->filter(function ($s) {
                    return $s['score'] >= 0.3;
                })
                ->take(3);

                $count++;

                if ($similars->count() >= 3 || $count >= $chunkCount) {
                    return false;
                }
            });

        return $similars->values();
    }

    protected function getSimilar($sourceContent, $targetContent)
    {
        $sourceMeta = $sourceContent->meta['keywords'];
        $targetMeta = $targetContent->meta['keywords'];

        if (! $sourceMeta || ! $targetMeta) {
            return false;
        }

        $sourceKeywords = collect($sourceMeta)
            ->filter(function ($keyword) {
                return $keyword['score'] >= 0.35;
            })
            ->keyBy('name');

        $targetKeywords = collect($targetMeta)
            ->filter(function ($keyword) {
                return $keyword['score'] >= 0.35;
            })
            ->keyBy('name');

        $similar = $sourceKeywords->keys()
            ->intersect($targetKeywords->keys())
            ->map(function ($key) use ($sourceKeywords, $targetKeywords, $targetContent) {
                return [
                    'title' => $targetContent->title,
                    'id' => $targetContent->id,
                    'source' => $sourceKeywords[$key],
                    'target' => $targetKeywords[$key],
                    'created' => $targetContent->created_at->diffForHumans(null, true),
                    'updated' => $targetContent->updated_at->diffForHumans(null, true),
                ];
            })
            ->values()
            ->unique('id');

        return $similar->isNotEmpty() ? $similar : false;
    }

    protected function getSimilarScore($similar)
    {
        $destinationCount = $similar->filter(function ($similarItem) {
            return $similarItem['source']['type'] == 'destination';
        })
        ->count();

        $topicCount = $similar->filter(function ($similarItem) {
            return $similarItem['source']['type'] == 'topic';
        })
        ->count();

        $scoreMap = [
        //   destinations
        //   0   1   2+
            [0.0, 0.3, 0.5],  // 0  topics
            [0.2, 0.5, 0.7],  // 1  topics
            [0.2, 0.7, 0.9],  // 2+ topics
        ];

        return $scoreMap
            [min([$topicCount, 2])]
            [min([$destinationCount, 2])];
    }
}
