<?php

namespace App\Console\Commands;

class ConvertNews extends ConvertBase
{

    protected $signature = 'convert:news';


    public function convert()
    {
        $nodes = $this->getNodes('story')
            ->join('content_type_story', 'content_type_story.nid', '=', 'node.nid')
            ->select(
                'node.*',
                'node_revisions.*',
                'content_type_story.*'
            )
            ->get();

        $this->info('Converting news');
        $this->output->progressStart(count($nodes));

        foreach($nodes as $node) {

            // Find the image embedded in body

            $frontImagePattern = "/.*\s*<!--\s*FRONTIMG:\s*(.*)\s*-->.*/";
      
            if (preg_match($frontImagePattern, $node->body)) {

                $node->body = preg_replace($frontImagePattern, '', $node->body);
            
            }

            $imagePattern = '/(https?:\/\/.*\.(?:png|jpg|jpeg|gif))/i';

            if (preg_match_all($imagePattern, $node->body, $imageMatches)) {
                
                $image = (isset($imageMatches[0]) && isset($imageMatches[0][0])) ? $imageMatches[0][0] : null;

            }

            // Convert the content

            if ($this->convertNode($node, '\App\Content', 'news')) {
      
                // Convert the image

                if ($image) {
                          
                    $this->convertRemoteImage($node->nid, $image, '\App\Content', 'news', 'photo');
                
                }

                // Convert the URL

                if ($url = $node->field_lyhiuudislink_url) {
                    
                    $this->convertUrl($node->nid, $url, '\App\Content');
                
                }

                $this->convertNodeDestinations($node);
                $this->convertNodeTopics($node);

            }
            
            $this->output->progressAdvance();

        }

        $this->output->progressFinish();
        
    }

    public function handle()
    {
        $this->convert();
    }

}
