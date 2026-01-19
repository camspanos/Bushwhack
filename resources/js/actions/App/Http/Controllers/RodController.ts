import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
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
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
* @route '/rods'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
* @route '/rods'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
* @route '/rods'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
* @route '/rods'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
* @route '/rods'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::index
* @see app/Http/Controllers/RodController.php:18
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
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::create
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::store
* @see app/Http/Controllers/RodController.php:35
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
* @see \App\Http\Controllers\RodController::store
* @see app/Http/Controllers/RodController.php:35
* @route '/rods'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::store
* @see app/Http/Controllers/RodController.php:35
* @route '/rods'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RodController::store
* @see app/Http/Controllers/RodController.php:35
* @route '/rods'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RodController::store
* @see app/Http/Controllers/RodController.php:35
* @route '/rods'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}'
*/
show.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}'
*/
show.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}'
*/
const showForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}'
*/
showForm.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::show
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}/edit'
*/
edit.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}/edit'
*/
edit.head = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}/edit'
*/
const editForm = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
* @route '/rods/{rod}/edit'
*/
editForm.get = (args: { rod: string | number } | [rod: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::edit
* @see app/Http/Controllers/RodController.php:0
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
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
export const update = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/rods/{rod}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
update.url = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { rod: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: typeof args.rod === 'object'
        ? args.rod.id
        : args.rod,
    }

    return update.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
update.put = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
update.patch = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
const updateForm = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
updateForm.put = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RodController::update
* @see app/Http/Controllers/RodController.php:50
* @route '/rods/{rod}'
*/
updateForm.patch = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\RodController::destroy
* @see app/Http/Controllers/RodController.php:67
* @route '/rods/{rod}'
*/
export const destroy = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/rods/{rod}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\RodController::destroy
* @see app/Http/Controllers/RodController.php:67
* @route '/rods/{rod}'
*/
destroy.url = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { rod: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { rod: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            rod: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        rod: typeof args.rod === 'object'
        ? args.rod.id
        : args.rod,
    }

    return destroy.definition.url
            .replace('{rod}', parsedArgs.rod.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::destroy
* @see app/Http/Controllers/RodController.php:67
* @route '/rods/{rod}'
*/
destroy.delete = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\RodController::destroy
* @see app/Http/Controllers/RodController.php:67
* @route '/rods/{rod}'
*/
const destroyForm = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\RodController::destroy
* @see app/Http/Controllers/RodController.php:67
* @route '/rods/{rod}'
*/
destroyForm.delete = (args: { rod: number | { id: number } } | [rod: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
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
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
* @route '/rods/stats/all'
*/
statistics.url = (options?: RouteQueryOptions) => {
    return statistics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
* @route '/rods/stats/all'
*/
statistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
* @route '/rods/stats/all'
*/
statistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: statistics.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
* @route '/rods/stats/all'
*/
const statisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
* @route '/rods/stats/all'
*/
statisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\RodController::statistics
* @see app/Http/Controllers/RodController.php:88
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

const RodController = { index, create, store, show, edit, update, destroy, statistics }

export default RodController