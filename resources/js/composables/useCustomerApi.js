import axios from 'axios';

/** Customer shop API helpers — uses Sanctum session cookies */
export function useCustomerApi() {
    const base = '/api/customer';

    return {
        getProducts: (params) => axios.get(`${base}/products`, { params }),
        getProduct: (id) => axios.get(`${base}/products/${id}`),
        suggestions: (q) => axios.get(`${base}/products-search/suggestions`, { params: { q } }),
        getCart: () => axios.get(`${base}/cart`),
        addToCart: (medicine_id, quantity = 1) => axios.post(`${base}/cart`, { medicine_id, quantity }),
        updateCart: (id, data) => axios.put(`${base}/cart/${id}`, data),
        removeFromCart: (id) => axios.delete(`${base}/cart/${id}`),
        applyCoupon: (code) => axios.post(`${base}/cart/coupon`, { code }),
        wishlist: () => axios.get(`${base}/wishlist`),
        addWishlist: (medicine_id) => axios.post(`${base}/wishlist`, { medicine_id }),
        removeWishlist: (medicineId) => axios.delete(`${base}/wishlist/${medicineId}`),
        moveToCart: (medicineId) => axios.post(`${base}/wishlist/${medicineId}/move-to-cart`),
        placeOrder: (payload) => axios.post(`${base}/orders`, payload),
        analyzeAi: (responses) => axios.post(`${base}/orders/analyze-ai`, { responses }),
        aiQuestions: () => axios.get(`${base}/orders/ai-questions`),
        cancelOrder: (id) => axios.post(`${base}/orders/${id}/cancel`),
        uploadPrescription: (formData) => axios.post(`${base}/prescriptions`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        }),
        addresses: () => axios.get(`${base}/addresses`),
        saveAddress: (data) => axios.post(`${base}/addresses`, data),
        notifications: () => axios.get(`${base}/notifications`),
        markRead: (id) => axios.post(`${base}/notifications/${id}/read`),
    };
}
