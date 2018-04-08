<?php

namespace App\Http\Regions;

class PhotoRow
{
    public function render($photos, $actions = [])
    {
        $content = $photos->map(function ($photo) {
            $component = component('PhotoCard')
                ->with('small', $photo->imagePreset('small_square'))
                ->with('large', $photo->imagePreset('large'))
                ->with('meta', trans('content.photo.meta', [
                    'title' => $photo->vars()->title,
                    'username' => $photo->user->vars()->name,
                    'created_at' => $photo->vars()->created_at,
                ]));

            if (request()->user() && request()->user()->hasRole('admin')) {
                $component
                        ->with('edit_status', true)
                        ->with('photo_id', $photo->id)
                        ->with('status', $photo->status)
                        ->with('button_title', trans('content.action.status.1.title'));
            }

            return $component;
        });

        if ($content->count() && $content->count() < 9) {
            $content = $content->merge(array_fill(
                0,
                9 - $content->count(),
                component('PhotoCard')->with('small', '/photos/image_none.svg')
            ));
        }

        if (! $content->count()) {
            $content = array_fill(
                0,
                9 - $content->count(),
                component('PhotoCard')->with('small', '/photos/image_none.svg')
            );
        }

        return component('PhotoRow')
            ->with('content', $content)
            ->with('actions', $actions)
            ->render();
    }
}
