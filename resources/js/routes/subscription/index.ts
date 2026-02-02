import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition, applyUrlDefaults } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
export const edit = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

edit.definition = {
    methods: ["get","head"],
    url: '/settings/subscription',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
edit.url = (options?: RouteQueryOptions) => {
    return edit.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
edit.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
edit.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: edit.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
const editForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
editForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::edit
* @see app/Http/Controllers/Settings/SubscriptionController.php:25
* @route '/settings/subscription'
*/
editForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: edit.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

edit.form = editForm

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::checkout
* @see app/Http/Controllers/Settings/SubscriptionController.php:49
* @route '/settings/subscription/checkout/{plan}'
*/
export const checkout = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(args, options),
    method: 'post',
})

checkout.definition = {
    methods: ["post"],
    url: '/settings/subscription/checkout/{plan}',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::checkout
* @see app/Http/Controllers/Settings/SubscriptionController.php:49
* @route '/settings/subscription/checkout/{plan}'
*/
checkout.url = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions) => {
    if (typeof args === 'string' || typeof args === 'number') {
        args = { plan: args }
    }

    if (typeof args === 'object' && !Array.isArray(args) && 'id' in args) {
        args = { plan: args.id }
    }

    if (Array.isArray(args)) {
        args = {
            plan: args[0],
        }
    }

    args = applyUrlDefaults(args)

    const parsedArgs = {
        plan: typeof args.plan === 'object'
        ? args.plan.id
        : args.plan,
    }

    return checkout.definition.url
            .replace('{plan}', parsedArgs.plan.toString())
            .replace(/\/+$/, '') + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::checkout
* @see app/Http/Controllers/Settings/SubscriptionController.php:49
* @route '/settings/subscription/checkout/{plan}'
*/
checkout.post = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: checkout.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::checkout
* @see app/Http/Controllers/Settings/SubscriptionController.php:49
* @route '/settings/subscription/checkout/{plan}'
*/
const checkoutForm = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(args, options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::checkout
* @see app/Http/Controllers/Settings/SubscriptionController.php:49
* @route '/settings/subscription/checkout/{plan}'
*/
checkoutForm.post = (args: { plan: number | { id: number } } | [plan: number | { id: number } ] | number | { id: number }, options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: checkout.url(args, options),
    method: 'post',
})

checkout.form = checkoutForm

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
export const success = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(options),
    method: 'get',
})

success.definition = {
    methods: ["get","head"],
    url: '/settings/subscription/success',
} satisfies RouteDefinition<["get","head"]>

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
success.url = (options?: RouteQueryOptions) => {
    return success.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
success.get = (options?: RouteQueryOptions): RouteDefinition<'get'> => ({
    url: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
success.head = (options?: RouteQueryOptions): RouteDefinition<'head'> => ({
    url: success.url(options),
    method: 'head',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
const successForm = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
successForm.get = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url(options),
    method: 'get',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::success
* @see app/Http/Controllers/Settings/SubscriptionController.php:78
* @route '/settings/subscription/success'
*/
successForm.head = (options?: RouteQueryOptions): RouteFormDefinition<'get'> => ({
    action: success.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'HEAD',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'get',
})

success.form = successForm

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::cancel
* @see app/Http/Controllers/Settings/SubscriptionController.php:132
* @route '/settings/subscription'
*/
export const cancel = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: cancel.url(options),
    method: 'delete',
})

cancel.definition = {
    methods: ["delete"],
    url: '/settings/subscription',
} satisfies RouteDefinition<["delete"]>

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::cancel
* @see app/Http/Controllers/Settings/SubscriptionController.php:132
* @route '/settings/subscription'
*/
cancel.url = (options?: RouteQueryOptions) => {
    return cancel.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::cancel
* @see app/Http/Controllers/Settings/SubscriptionController.php:132
* @route '/settings/subscription'
*/
cancel.delete = (options?: RouteQueryOptions): RouteDefinition<'delete'> => ({
    url: cancel.url(options),
    method: 'delete',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::cancel
* @see app/Http/Controllers/Settings/SubscriptionController.php:132
* @route '/settings/subscription'
*/
const cancelForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cancel.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::cancel
* @see app/Http/Controllers/Settings/SubscriptionController.php:132
* @route '/settings/subscription'
*/
cancelForm.delete = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: cancel.url({
        [options?.mergeQuery ? 'mergeQuery' : 'query']: {
            _method: 'DELETE',
            ...(options?.query ?? options?.mergeQuery ?? {}),
        }
    }),
    method: 'post',
})

cancel.form = cancelForm

const subscription = {
    edit: Object.assign(edit, edit),
    checkout: Object.assign(checkout, checkout),
    success: Object.assign(success, success),
    cancel: Object.assign(cancel, cancel),
}

export default subscription