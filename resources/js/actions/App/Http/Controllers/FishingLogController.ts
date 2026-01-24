import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
export const availableYears = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: availableYears.url(options),
    method: 'get',
})

availableYears.definition = {
    methods: ["get","head"],
    url: '/fishing-logs/available-years',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
availableYears.url = (options?: RouteQueryOptions) => {
    return availableYears.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
availableYears.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: availableYears.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
availableYears.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: availableYears.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
const availableYearsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: availableYears.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
availableYearsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: availableYears.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::availableYears
* @see app/Http/Controllers/FishingLogController.php:157
* @route '/fishing-logs/available-years'
*/
availableYearsForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: availableYears.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

availableYears.form = availableYearsForm

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/fishing-logs',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::index
* @see app/Http/Controllers/FishingLogController.php:19
* @route '/fishing-logs'
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
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/fishing-logs/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::create
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/create'
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
* @see \App\Http\Controllers\FishingLogController::store
* @see app/Http/Controllers/FishingLogController.php:38
* @route '/fishing-logs'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/fishing-logs',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FishingLogController::store
* @see app/Http/Controllers/FishingLogController.php:38
* @route '/fishing-logs'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::store
* @see app/Http/Controllers/FishingLogController.php:38
* @route '/fishing-logs'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishingLogController::store
* @see app/Http/Controllers/FishingLogController.php:38
* @route '/fishing-logs'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishingLogController::store
* @see app/Http/Controllers/FishingLogController.php:38
* @route '/fishing-logs'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
export const show = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/fishing-logs/{fishing_log}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
show.url = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fishing_log: args }
    }

    if (Array.isArray(args)) {
        args = {
            fishing_log: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fishing_log: args.fishing_log,
    }

    return show.definition.url
            .replace('{fishing_log}', parsedArgs.fishing_log.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
show.get = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
show.head = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
const showForm = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
showForm.get = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::show
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}'
*/
showForm.head = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
export const edit = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/fishing-logs/{fishing_log}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
edit.url = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fishing_log: args }
    }

    if (Array.isArray(args)) {
        args = {
            fishing_log: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fishing_log: args.fishing_log,
    }

    return edit.definition.url
            .replace('{fishing_log}', parsedArgs.fishing_log.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
edit.get = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
edit.head = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
const editForm = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
editForm.get = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishingLogController::edit
* @see app/Http/Controllers/FishingLogController.php:0
* @route '/fishing-logs/{fishing_log}/edit'
*/
editForm.head = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
export const update = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/fishing-logs/{fishing_log}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
update.url = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fishing_log: args }
    }

    if (Array.isArray(args)) {
        args = {
            fishing_log: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fishing_log: args.fishing_log,
    }

    return update.definition.url
            .replace('{fishing_log}', parsedArgs.fishing_log.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
update.put = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
update.patch = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
const updateForm = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
updateForm.put = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishingLogController::update
* @see app/Http/Controllers/FishingLogController.php:101
* @route '/fishing-logs/{fishing_log}'
*/
updateForm.patch = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\FishingLogController::destroy
* @see app/Http/Controllers/FishingLogController.php:138
* @route '/fishing-logs/{fishing_log}'
*/
export const destroy = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/fishing-logs/{fishing_log}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FishingLogController::destroy
* @see app/Http/Controllers/FishingLogController.php:138
* @route '/fishing-logs/{fishing_log}'
*/
destroy.url = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fishing_log: args }
    }

    if (Array.isArray(args)) {
        args = {
            fishing_log: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fishing_log: args.fishing_log,
    }

    return destroy.definition.url
            .replace('{fishing_log}', parsedArgs.fishing_log.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishingLogController::destroy
* @see app/Http/Controllers/FishingLogController.php:138
* @route '/fishing-logs/{fishing_log}'
*/
destroy.delete = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FishingLogController::destroy
* @see app/Http/Controllers/FishingLogController.php:138
* @route '/fishing-logs/{fishing_log}'
*/
const destroyForm = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishingLogController::destroy
* @see app/Http/Controllers/FishingLogController.php:138
* @route '/fishing-logs/{fishing_log}'
*/
destroyForm.delete = (args: { fishing_log: string | number } | [fishing_log: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const FishingLogController = { availableYears, index, create, store, show, edit, update, destroy }

export default FishingLogController