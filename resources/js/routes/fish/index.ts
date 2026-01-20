import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/fish',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::index
* @see app/Http/Controllers/FishController.php:18
* @route '/fish'
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
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/fish/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::create
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/create'
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
* @see \App\Http\Controllers\FishController::store
* @see app/Http/Controllers/FishController.php:35
* @route '/fish'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/fish',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\FishController::store
* @see app/Http/Controllers/FishController.php:35
* @route '/fish'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::store
* @see app/Http/Controllers/FishController.php:35
* @route '/fish'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishController::store
* @see app/Http/Controllers/FishController.php:35
* @route '/fish'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishController::store
* @see app/Http/Controllers/FishController.php:35
* @route '/fish'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
export const show = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/fish/{fish}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
show.url = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fish: args }
    }

    if (Array.isArray(args)) {
        args = {
            fish: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fish: args.fish,
    }

    return show.definition.url
            .replace('{fish}', parsedArgs.fish.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
show.get = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
show.head = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
const showForm = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
showForm.get = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::show
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}'
*/
showForm.head = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
export const edit = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/fish/{fish}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
edit.url = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fish: args }
    }

    if (Array.isArray(args)) {
        args = {
            fish: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fish: args.fish,
    }

    return edit.definition.url
            .replace('{fish}', parsedArgs.fish.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
edit.get = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
edit.head = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
const editForm = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
editForm.get = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::edit
* @see app/Http/Controllers/FishController.php:0
* @route '/fish/{fish}/edit'
*/
editForm.head = (args: { fish: string | number } | [fish: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
export const update = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/fish/{fish}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
update.url = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fish: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { fish: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            fish: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fish: typeof args.fish === 'object'
        ? args.fish.id
        : args.fish,
    }

    return update.definition.url
            .replace('{fish}', parsedArgs.fish.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
update.put = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
update.patch = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
const updateForm = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
updateForm.put = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishController::update
* @see app/Http/Controllers/FishController.php:78
* @route '/fish/{fish}'
*/
updateForm.patch = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\FishController::destroy
* @see app/Http/Controllers/FishController.php:110
* @route '/fish/{fish}'
*/
export const destroy = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/fish/{fish}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\FishController::destroy
* @see app/Http/Controllers/FishController.php:110
* @route '/fish/{fish}'
*/
destroy.url = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { fish: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { fish: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            fish: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        fish: typeof args.fish === 'object'
        ? args.fish.id
        : args.fish,
    }

    return destroy.definition.url
            .replace('{fish}', parsedArgs.fish.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::destroy
* @see app/Http/Controllers/FishController.php:110
* @route '/fish/{fish}'
*/
destroy.delete = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\FishController::destroy
* @see app/Http/Controllers/FishController.php:110
* @route '/fish/{fish}'
*/
const destroyForm = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\FishController::destroy
* @see app/Http/Controllers/FishController.php:110
* @route '/fish/{fish}'
*/
destroyForm.delete = (args: { fish: number | { id: number } } | [fish: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
export const statistics = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: statistics.url(options),
    method: 'get',
})

statistics.definition = {
    methods: ["get","head"],
    url: '/fish/stats/all',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
statistics.url = (options?: RouteQueryOptions) => {
    return statistics.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
statistics.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
statistics.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: statistics.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
const statisticsForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
*/
statisticsForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: statistics.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\FishController::statistics
* @see app/Http/Controllers/FishController.php:131
* @route '/fish/stats/all'
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

const fish = {
    index: Object.assign(index, index),
    create: Object.assign(create, create),
    store: Object.assign(store, store),
    show: Object.assign(show, show),
    edit: Object.assign(edit, edit),
    update: Object.assign(update, update),
    destroy: Object.assign(destroy, destroy),
    statistics: Object.assign(statistics, statistics),
}

export default fish