import { queryParams, type RouteQueryOptions, type RouteDefinition, type RouteFormDefinition } from './../../wayfinder'
/**
* @see \App\Http\Controllers\Settings\SubscriptionController::payment
* @see app/Http/Controllers/Settings/SubscriptionController.php:168
* @route '/webhooks/payment'
*/
export const payment = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payment.url(options),
    method: 'post',
})

payment.definition = {
    methods: ["post"],
    url: '/webhooks/payment',
} satisfies RouteDefinition<["post"]>

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::payment
* @see app/Http/Controllers/Settings/SubscriptionController.php:168
* @route '/webhooks/payment'
*/
payment.url = (options?: RouteQueryOptions) => {
    return payment.definition.url + queryParams(options)
}

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::payment
* @see app/Http/Controllers/Settings/SubscriptionController.php:168
* @route '/webhooks/payment'
*/
payment.post = (options?: RouteQueryOptions): RouteDefinition<'post'> => ({
    url: payment.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::payment
* @see app/Http/Controllers/Settings/SubscriptionController.php:168
* @route '/webhooks/payment'
*/
const paymentForm = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: payment.url(options),
    method: 'post',
})

/**
* @see \App\Http\Controllers\Settings\SubscriptionController::payment
* @see app/Http/Controllers/Settings/SubscriptionController.php:168
* @route '/webhooks/payment'
*/
paymentForm.post = (options?: RouteQueryOptions): RouteFormDefinition<'post'> => ({
    action: payment.url(options),
    method: 'post',
})

payment.form = paymentForm

const webhooks = {
    payment: Object.assign(payment, payment),
}

export default webhooks