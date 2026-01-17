import axios from 'axios';

// Configure axios to include CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');

if (token) {
    axios.defaults.headers.common['X-CSRF-TOKEN'] = (token as HTMLMetaElement).content;
} else {
    console.error('CSRF token not found');
}

// Set default headers
axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

export default axios;

