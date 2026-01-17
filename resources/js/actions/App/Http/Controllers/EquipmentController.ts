import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../../../wayfinder'
/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
export const index = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

index.definition = {
    methods: ["get","head"],
    url: '/equipment',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
index.url = (options?: RouteQueryOptions) => {
    return index.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
index.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
index.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: index.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
const indexForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
*/
indexForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: index.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::index
* @see app/Http/Controllers/EquipmentController.php:19
* @route '/equipment'
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
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
export const create = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

create.definition = {
    methods: ["get","head"],
    url: '/equipment/create',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
create.url = (options?: RouteQueryOptions) => {
    return create.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
create.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
create.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: create.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
const createForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
*/
createForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: create.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::create
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/create'
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
* @see \App\Http\Controllers\EquipmentController::store
* @see app/Http/Controllers/EquipmentController.php:37
* @route '/equipment'
*/
export const store = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

store.definition = {
    methods: ["post"],
    url: '/equipment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\EquipmentController::store
* @see app/Http/Controllers/EquipmentController.php:37
* @route '/equipment'
*/
store.url = (options?: RouteQueryOptions) => {
    return store.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::store
* @see app/Http/Controllers/EquipmentController.php:37
* @route '/equipment'
*/
store.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EquipmentController::store
* @see app/Http/Controllers/EquipmentController.php:37
* @route '/equipment'
*/
const storeForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EquipmentController::store
* @see app/Http/Controllers/EquipmentController.php:37
* @route '/equipment'
*/
storeForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: store.url(options),
    method: 'post',
})

store.form = storeForm

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
export const show = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

show.definition = {
    methods: ["get","head"],
    url: '/equipment/{equipment}',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
show.url = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { equipment: args }
    }

    if (Array.isArray(args)) {
        args = {
            equipment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        equipment: args.equipment,
    }

    return show.definition.url
            .replace('{equipment}', parsedArgs.equipment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
show.get = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
show.head = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: show.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
const showForm = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
showForm.get = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: show.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::show
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
showForm.head = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
export const edit = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/equipment/{equipment}/edit',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
edit.url = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { equipment: args }
    }

    if (Array.isArray(args)) {
        args = {
            equipment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        equipment: args.equipment,
    }

    return edit.definition.url
            .replace('{equipment}', parsedArgs.equipment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
edit.get = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
edit.head = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(args, options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
const editForm = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
editForm.get = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(args, options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\EquipmentController::edit
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}/edit'
*/
editForm.head = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
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
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
export const update = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

update.definition = {
    methods: ["put","patch"],
    url: '/equipment/{equipment}',
} satisfies RouteDefinition<["put","patch"]>

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
update.url = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { equipment: args }
    }

    if (Array.isArray(args)) {
        args = {
            equipment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        equipment: args.equipment,
    }

    return update.definition.url
            .replace('{equipment}', parsedArgs.equipment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
update.put = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'put'> => ({
    url: update.url(args, options),
    method: 'put',
})

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
update.patch = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'patch'> => ({
    url: update.url(args, options),
    method: 'patch',
})

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
const updateForm = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
updateForm.put = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: update.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'PUT',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EquipmentController::update
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
updateForm.patch = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
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
* @see \App\Http\Controllers\EquipmentController::destroy
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
export const destroy = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

destroy.definition = {
    methods: ["delete"],
    url: '/equipment/{equipment}',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\EquipmentController::destroy
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
destroy.url = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { equipment: args }
    }

    if (Array.isArray(args)) {
        args = {
            equipment: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        equipment: args.equipment,
    }

    return destroy.definition.url
            .replace('{equipment}', parsedArgs.equipment.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\EquipmentController::destroy
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
destroy.delete = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: destroy.url(args, options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\EquipmentController::destroy
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
const destroyForm = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\EquipmentController::destroy
* @see app/Http/Controllers/EquipmentController.php:0
* @route '/equipment/{equipment}'
*/
destroyForm.delete = (args: { equipment: string | number } | [equipment: string | number ] | string | number, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: destroy.url(args, {
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

destroy.form = destroyForm

const EquipmentController = { index, create, store, show, edit, update, destroy }

export default EquipmentController