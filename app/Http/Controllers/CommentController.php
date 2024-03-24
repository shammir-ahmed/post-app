<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\CommentFormRequest;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Post $post)
    {
        $comments = Comment::query();

        // check is there any filteration parameter
        if (request()->has('author') && strlen(request()->author) > 0) {
            $author = request()->author;
            $comments = $comments->where('author', 'LIKE', '%.$author.%');
        }

        $comments = $comments->get();

        return JsonResource::collection($comments);
    }

    /**
     * Show the form for creating a new resource.
     */

    /**
     * Store a newly created resource in storage.
     */
    // public function store(CommentFormRequest $request)
    // {
    //     return new JsonResource(Comment::create($request->validated())); // 201
    // }


    public function store(CommentFormRequest $request, Post $post)
    {
        $comment = $post->comments()->create($request->validated());
        
        if ($comment) {
            $post->increment('comment_count'); 
        }

        return new JsonResource($comment);
    }

    /**
     * Display the specified resource.
     */
    public function show(Comment $comment)
    {
        return new JsonResource($comment);
    }

    /**
     * Show the form for editing the specified resource.
     */
   
    /**
     * Update the specified resource in storage.
     */
    public function update(CommentFormRequest $request, Comment $comment)
    {
        $comment->fill($request->validated());
        $comment->save(); // true or false

        return new JsonResource($comment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Comment $comment)
    {
        $comment->delete();

        return response()->json([], 204);
    }
}

