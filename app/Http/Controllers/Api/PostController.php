<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\EditPostRequest;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{

public function getPostAll(Request $request)
{
    try {
        $posts = Post::query();
        $search = $request->input('search');
        if ($search) {
            $posts = $posts->where('titre', 'LIKE', '%' . $search . '%');
        }
        $result = $posts->get();
        return response()->json([
            'success' => true,
            'message' => 'Tous les posts récupérés sans probleme',
            'data' => $result
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des posts',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function getPostbyId(Request $request,$id)
{
    try {
        $posts = Post::find($id);
        if ($posts) {
            return response()->json([
                'success' => true,
                'message' => 'voici le post',
                'data' => $posts
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Post Introuvable'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la récupération des posts',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function addPost(CreatePostRequest $request)
{
    try {
        $post = new Post();
        $post->titre = $request->titre;
        $post->description = $request->description;
        $post->user_id = auth()->user()->id;
        $post->save();

        return response()->json([
            'success' => true,
            'message' => 'Post ajouté avec succès',
            'data' => $post
        ], 200);


    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de l\'ajout du post',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function editPost(EditPostRequest $request, $id){

    try {
        $post = Post::find($id);

        if ($post) {

            $post->titre = $request->titre;
            $post->description = $request->description;
            $post->save();

            return response()->json([
            'success' => true,
            'message' => 'Post Modifié avec succès',
            'data' => $post
            ], 200);

        } else {
            return response()->json([
                'success' => true,
                'message' => 'Post Introuvable'
            ], 404);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la modification du post',
            'error' => $e->getMessage()
        ], 500);
    }

}

public function deletePost($id){

    try {
        $post = Post::find($id);

        if ($post) {
            $post->delete();

            return response()->json([
                'success' => true,
                'message' => 'Post supprimer avec succès',
                'data' => $post
            ], 200);
        } else {
            return response()->json([
                'success' => true,
                'message' => 'Post Introuvable'
            ], 404);
        }


    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur lors de la suppression du post',
            'error' => $e->getMessage()
        ], 500);
    }

}
}
