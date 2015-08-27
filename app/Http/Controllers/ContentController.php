<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Auth;
use Imageconv;

use App\Content;
use App\Destination;
use App\Topic;

class ContentController extends Controller
{

    public function index(Request $request, $type)
    {

        $contents = Content::whereType($type)
            ->with(config("content_$type.index.with"))
            ->latest(config("content_$type.index.latest"));
    
        if ($request->destination) {

            $descendants = Destination::find($request->destination)
                ->descendantsAndSelf()
                ->lists('id');

            $contents = $contents
                ->join('content_destination', 'content_destination.content_id', '=', 'contents.id')
                ->select('contents.*')
                ->whereIn('content_destination.destination_id', $descendants);
        }   

        if ($request->topic) {

            $contents = $contents
                ->join('content_topic', 'content_topic.content_id', '=', 'contents.id')
                ->select('contents.*')
                ->where('content_topic.topic_id', '=', $request->topic);
        
        } 

        $contents = $contents->simplePaginate(config("content_$type.index.paginate"));

        $destinations = Destination::getNames($type);
        $topics = Topic::getNames($type);

        return \View::make("pages.content.$type.index")
            ->with('contents', $contents)
            ->with('type', $type)
            ->with('destination', $request->destination)
            ->with('destinations', $destinations)
            ->with('topic', $request->topic)
            ->with('topics', $topics)
            ->render();
    
    }


    public function show($type, $id)
    {
        $content = \App\Content::with('user', 'comments', 'comments.user', 'flags', 'comments.flags', 'flags.user', 'comments.flags.user', 'destinations', 'topics', 'carriers')
            ->findorFail($id);
             
        $comments = $content->comments->filter(function ($comment) {
            return $comment->status || (Auth::check() && Auth::user()->hasRole('admin'));
        });

        $view = view()->exists("pages.content.$type.show") ? "pages.content.$type.show" : 'pages.content.show';

        return \View::make($view)
            ->with('content', $content)
            ->with('comments', $comments)
            ->with('type', $type)
            ->render();
    }

    public function create($type)
    {

        return \View::make("pages.content.edit")
            ->with('fields', config("content_$type.edit.fields"))
            ->with('title', trans('content.create.title'))
            ->with('url', route('content.store', [$type]))
            ->with('type', $type)
            ->render();

    }

    public function store(Request $request, $type)
    {
        $this->validate($request, config("content_$type.edit.validate"));

        $fields = [
            'type' => $type,
            'status' => 1
        ];

        if ($request->hasFile('file')) {
            
            $image = $this->storeImage($request->file('file'), $type);
            $fields['image'] = $image;

        }

        $content = Auth::user()->contents()->create(array_merge($request->all(), $fields));

        return redirect()
            ->route('content.index', [$type])
            ->with('status', trans("content.store.status", ['title' => $content->title]));

    }

    public function storeImage($file, $type)
    {
        $random_str = str_random(5);

        $fileName = $random_str.'_'.$file->getClientOriginalName();
        $path = public_path() . "/images/$type/";
        $smallPath = public_path() . "/images/$type/small/";
        
        $file->move($path, $fileName);

        Imageconv::make($path . $fileName)
            ->resize(300, null, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save($smallPath . $fileName);
        
        return $fileName;
    
    }

    public function edit($type, $id)
    {

        $content = \App\Content::findorFail($id);

        return \View::make("pages.content.edit")
            ->with('title', trans('content.create.title'))
            ->with('fields', config("content_$type.edit.fields"))
            ->with('content', $content)
            ->with('method', 'put')
            ->with('url', route('content.update', [$content->type, $content]))
            ->with('type', $type)
            ->render();

    }

    public function update(Request $request, $type, $id)
    {

        $content = \App\Content::findorFail($id);

        $this->validate($request, config("content_$type.edit.validate"));

        if ($request->hasFile('file')) {
            
            $image = $this->storeImage($request->file('file'), $type);
            $fields['image'] = $image;

        }

        $content->update(array_merge($fields, $request->all()));

        return redirect()
            ->route('content.show', [$type, $content])
            ->with('status', trans("content.update.status", ['title' => $content->title]));

    }

    public function status($type, $id, $status)
    {

        $content = \App\Content::findorFail($id);

        if ($status == 0 || $status == 1) {

            $content->status = $status;
            $content->save();

            return redirect()
                ->route('content.show', [$type, $content])
                ->with('status', trans('content.action.' . config("site.statuses.$status") . '.status', [
                    'title' => $content->title
                ]));
        }
        
        return back();

    }

    public function redirect($path)
    {
        $alias = \DB::table('content_alias')
            ->whereAlias('content/' . $path)
            ->first();

        if ($alias) {
            return redirect('content/' . $alias->content_id, 301);
        }

        abort(404);
    }

    public function filter(Request $request, $type)
    {

        return redirect()->route(
            'content.index',
            [$type,
            'destination' => $request->destination ? $request->destination : null,
            'topic' => $request->topic ? $request->topic : null,
            ]
        );

    }

}
