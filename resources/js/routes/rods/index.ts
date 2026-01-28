import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
export const statistics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: statistics.url(options),
    method: 'get',
})

statistics.definition = {
    methods: ["get","head"],
    url: '/rods/stats/all',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
statistics.url = (options?: RouteQueryOptions) => {
    return statistics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
statistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
statistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: statistics.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
const statisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
statisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::statistics
* @see app/Http/Controllers/UserRodsController.php:138
* @route '/rods/stats/all'
*/
statisticsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

statistics.form = statisticsForm

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/rods',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::index
* @see app/Http/Controllers/UserRodsController.php:19
* @route '/rods'
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
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/rods/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::create
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/create'
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
* @see \App\Http\Controllers\UserRodsController::store
* @see app/Http/Controllers/UserRodsController.php:36
* @route '/rods'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/rods',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\UserRodsController::store
* @see app/Http/Controllers/UserRodsController.php:36
* @route '/rods'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::store
* @see app/Http/Controllers/UserRodsController.php:36
* @route '/rods'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserRodsController::store
* @see app/Http/Controllers/UserRodsController.php:36
* @route '/rods'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserRodsController::store
* @see app/Http/Controllers/UserRodsController.php:36
* @route '/rods'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
export const show = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/rods/{rod}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
show.url = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: args.rod,
    }

    return show.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
show.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
show.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
const showForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
showForm.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::show
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}'
*/
showForm.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
export const edit = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/rods/{rod}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
edit.url = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: args.rod,
    }

    return edit.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
edit.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
edit.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
const editForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
editForm.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\UserRodsController::edit
* @see app/Http/Controllers/UserRodsController.php:0
* @route '/rods/{rod}/edit'
*/
editForm.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
export const update = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/rods/{rod}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
update.url = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: args.rod,
    }

    return update.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
update.put = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
update.patch = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
const updateForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
updateForm.put = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserRodsController::update
* @see app/Http/Controllers/UserRodsController.php:82
* @route '/rods/{rod}'
*/
updateForm.patch = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\UserRodsController::destroy
* @see app/Http/Controllers/UserRodsController.php:117
* @route '/rods/{rod}'
*/
export const destroy = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/rods/{rod}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\UserRodsController::destroy
* @see app/Http/Controllers/UserRodsController.php:117
* @route '/rods/{rod}'
*/
destroy.url = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: args.rod,
    }

    return destroy.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\UserRodsController::destroy
* @see app/Http/Controllers/UserRodsController.php:117
* @route '/rods/{rod}'
*/
destroy.delete = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\UserRodsController::destroy
* @see app/Http/Controllers/UserRodsController.php:117
* @route '/rods/{rod}'
*/
const destroyForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\UserRodsController::destroy
* @see app/Http/Controllers/UserRodsController.php:117
* @route '/rods/{rod}'
*/
destroyForm.delete = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const rods = {
    statistics: Object.assign(statistics, statistics),
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
}

export default rods