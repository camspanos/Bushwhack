import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/friends',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::index
* @see app/Http/Controllers/FriendController.php:18
* @route '/friends'
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
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/friends/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::create
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/create'
*/
createForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

create.form = createForm

/**
* @see \App\Http\Controllers\FriendController::store
* @see app/Http/Controllers/FriendController.php:34
* @route '/friends'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/friends',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FriendController::store
* @see app/Http/Controllers/FriendController.php:34
* @route '/friends'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::store
* @see app/Http/Controllers/FriendController.php:34
* @route '/friends'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FriendController::store
* @see app/Http/Controllers/FriendController.php:34
* @route '/friends'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FriendController::store
* @see app/Http/Controllers/FriendController.php:34
* @route '/friends'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
export const show = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/friends/{friend}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
show.url = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { friend: args }
    }

    if (Array.isArray(args)) {
        args = {
            friend: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        friend: args.friend,
    }

    return show.definition.url
            .replace('{friend}', parsedArgs.friend.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
show.get = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
show.head = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
const showForm = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
showForm.get = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::show
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
showForm.head = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

show.form = showForm

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
export const edit = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/friends/{friend}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
edit.url = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { friend: args }
    }

    if (Array.isArray(args)) {
        args = {
            friend: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        friend: args.friend,
    }

    return edit.definition.url
            .replace('{friend}', parsedArgs.friend.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
edit.get = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
edit.head = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
const editForm = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
editForm.get = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FriendController::edit
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}/edit'
*/
editForm.head = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
export const update = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/friends/{friend}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
update.url = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { friend: args }
    }

    if (Array.isArray(args)) {
        args = {
            friend: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        friend: args.friend,
    }

    return update.definition.url
            .replace('{friend}', parsedArgs.friend.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
update.put = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
update.patch = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
const updateForm = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
updateForm.put = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FriendController::update
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
updateForm.patch = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PATCH',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

update.form = updateForm

/**
* @see \App\Http\Controllers\FriendController::destroy
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
export const destroy = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/friends/{friend}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FriendController::destroy
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
destroy.url = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { friend: args }
    }

    if (Array.isArray(args)) {
        args = {
            friend: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        friend: args.friend,
    }

    return destroy.definition.url
            .replace('{friend}', parsedArgs.friend.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FriendController::destroy
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
destroy.delete = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FriendController::destroy
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
const destroyForm = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FriendController::destroy
* @see app/Http/Controllers/FriendController.php:0
* @route '/friends/{friend}'
*/
destroyForm.delete = (args: { friend: string | number } | [friend: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const FriendController = { index, create, store, show, edit, update, destroy }

export default FriendController