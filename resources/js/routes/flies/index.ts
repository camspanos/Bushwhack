import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/flies',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::index
* @see app/Http/Controllers/FlyController.php:19
* @route '/flies'
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
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/flies/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::create
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/create'
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
* @see \App\Http\Controllers\FlyController::store
* @see app/Http/Controllers/FlyController.php:37
* @route '/flies'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/flies',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FlyController::store
* @see app/Http/Controllers/FlyController.php:37
* @route '/flies'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::store
* @see app/Http/Controllers/FlyController.php:37
* @route '/flies'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FlyController::store
* @see app/Http/Controllers/FlyController.php:37
* @route '/flies'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FlyController::store
* @see app/Http/Controllers/FlyController.php:37
* @route '/flies'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
export const show = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/flies/{fly}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
show.url = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fly: args }
    }

    if (Array.isArray(args)) {
        args = {
            fly: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fly: args.fly,
    }

    return show.definition.url
            .replace('{fly}', parsedArgs.fly.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
show.get = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
show.head = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
const showForm = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
showForm.get = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::show
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
showForm.head = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
export const edit = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/flies/{fly}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
edit.url = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fly: args }
    }

    if (Array.isArray(args)) {
        args = {
            fly: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fly: args.fly,
    }

    return edit.definition.url
            .replace('{fly}', parsedArgs.fly.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
edit.get = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
edit.head = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
const editForm = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
editForm.get = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FlyController::edit
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}/edit'
*/
editForm.head = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
export const update = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/flies/{fly}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
update.url = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fly: args }
    }

    if (Array.isArray(args)) {
        args = {
            fly: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fly: args.fly,
    }

    return update.definition.url
            .replace('{fly}', parsedArgs.fly.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
update.put = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
update.patch = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
const updateForm = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
updateForm.put = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FlyController::update
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
updateForm.patch = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\FlyController::destroy
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
export const destroy = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/flies/{fly}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FlyController::destroy
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
destroy.url = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fly: args }
    }

    if (Array.isArray(args)) {
        args = {
            fly: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fly: args.fly,
    }

    return destroy.definition.url
            .replace('{fly}', parsedArgs.fly.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FlyController::destroy
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
destroy.delete = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FlyController::destroy
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
const destroyForm = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FlyController::destroy
* @see app/Http/Controllers/FlyController.php:0
* @route '/flies/{fly}'
*/
destroyForm.delete = (args: { fly: string | number } | [fly: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const flies = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default flies