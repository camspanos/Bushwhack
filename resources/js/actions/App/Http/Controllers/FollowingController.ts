import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/following',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::index
* @see app/Http/Controllers/FollowingController.php:16
* @route '/following'
*/
indexForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

index.form = indexForm

/**
* @see \App\Http\Controllers\FollowingController::follow
* @see app/Http/Controllers/FollowingController.php:42
* @route '/users/{user}/follow'
*/
export const follow = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: follow.url(args, options),
    method: 'post',
})

follow.definition = {
    methods: ["post"],
    url: '/users/{user}/follow',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FollowingController::follow
* @see app/Http/Controllers/FollowingController.php:42
* @route '/users/{user}/follow'
*/
follow.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return follow.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FollowingController::follow
* @see app/Http/Controllers/FollowingController.php:42
* @route '/users/{user}/follow'
*/
follow.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: follow.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FollowingController::follow
* @see app/Http/Controllers/FollowingController.php:42
* @route '/users/{user}/follow'
*/
const followForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: follow.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FollowingController::follow
* @see app/Http/Controllers/FollowingController.php:42
* @route '/users/{user}/follow'
*/
followForm.post = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: follow.url(args, options),
    method: 'post',
})

follow.form = followForm

/**
* @see \App\Http\Controllers\FollowingController::unfollow
* @see app/Http/Controllers/FollowingController.php:63
* @route '/users/{user}/unfollow'
*/
export const unfollow = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: unfollow.url(args, options),
    method: 'delete',
})

unfollow.definition = {
    methods: ["delete"],
    url: '/users/{user}/unfollow',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FollowingController::unfollow
* @see app/Http/Controllers/FollowingController.php:63
* @route '/users/{user}/unfollow'
*/
unfollow.url = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { user: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { user: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            user: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        user: typeof args.user === 'object'
        ? args.user.id
        : args.user,
    }

    return unfollow.definition.url
            .replace('{user}', parsedArgs.user.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FollowingController::unfollow
* @see app/Http/Controllers/FollowingController.php:63
* @route '/users/{user}/unfollow'
*/
unfollow.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: unfollow.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FollowingController::unfollow
* @see app/Http/Controllers/FollowingController.php:63
* @route '/users/{user}/unfollow'
*/
const unfollowForm = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: unfollow.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FollowingController::unfollow
* @see app/Http/Controllers/FollowingController.php:63
* @route '/users/{user}/unfollow'
*/
unfollowForm.delete = (args: { user: number | { id: number } } | [user: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: unfollow.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

unfollow.form = unfollowForm

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
export const search = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

search.definition = {
    methods: ["get","head"],
    url: '/users/search',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
search.url = (options?: RouteQueryOptions) => {
    return search.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
search.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
search.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: search.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
const searchForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
searchForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FollowingController::search
* @see app/Http/Controllers/FollowingController.php:75
* @route '/users/search'
*/
searchForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: search.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

search.form = searchForm

const FollowingController = { index, follow, unfollow, search }

export default FollowingController