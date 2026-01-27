<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FollowingController extends Controller
{
    /**
     * Display the list of users that the authenticated user is following.
     */
    public function index(): Response
    {
        $following = auth()->user()->following()
            ->select('users.id', 'users.name', 'users.email', 'users.created_at')
            ->withPivot('created_at')
            ->orderBy('user_follows.created_at', 'desc')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'member_since' => $user->created_at->format('M Y'),
                    'following_since' => $user->pivot->created_at->format('M d, Y'),
                ];
            })
            ->values(); // Reset array keys to ensure proper JSON array

        return Inertia::render('Following', [
            'following' => $following,
        ]);
    }

    /**
     * Follow a user.
     */
    public function follow(User $user): JsonResponse
    {
        $authUser = auth()->user();

        // Check if user can follow this specific user
        if (!$authUser->canFollow($user)) {
            return response()->json([
                'message' => 'Free users can only follow the default user. Upgrade to premium to follow other users.',
            ], 403);
        }

        $authUser->follow($user);

        return response()->json([
            'message' => 'Successfully followed ' . $user->name,
        ]);
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $user): JsonResponse
    {
        auth()->user()->unfollow($user);

        return response()->json([
            'message' => 'Successfully unfollowed ' . $user->name,
        ]);
    }

    /**
     * Search for users to follow.
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('query');
        $authUser = auth()->user();

        $users = User::where('id', '!=', $authUser->id)
            ->where('allow_followers', true)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->select('id', 'name', 'email', 'created_at')
            ->limit(10)
            ->get()
            ->map(function ($user) use ($authUser) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'member_since' => $user->created_at->format('M Y'),
                    'is_following' => $authUser->isFollowing($user),
                    'can_follow' => $authUser->canFollow($user),
                ];
            });

        return response()->json($users);
    }
}
