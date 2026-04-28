import axios from "axios";

const base = process.env.REACT_APP_API_URL || process.env.REACT_APP_API_BASE || "http://127.0.0.1:8000/api";

const api = axios.create({
  baseURL: base,
  headers: {
    Accept: "application/json",
  }
});

// attach token from localStorage if present
const token = localStorage.getItem('rp_token');
if (token) {
  api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

export function setAuthToken(token) {
  if (token) {
    localStorage.setItem('rp_token', token);
    api.defaults.headers.common['Authorization'] = `Bearer ${token}`;
  } else {
    localStorage.removeItem('rp_token');
    delete api.defaults.headers.common['Authorization'];
  }
}

// Global response interceptor: handle 401 unauthenticated responses
api.interceptors.response.use(
  (response) => response,
  (error) => {
    try {
      if (error?.response?.status === 401) {
        // clear stored token and notify user
        try { localStorage.removeItem('rp_token'); delete api.defaults.headers.common['Authorization']; } catch (e) {}
        // show friendly message
        try { window.alert('Session expired or unauthenticated. Please log in again.'); } catch (e) {}
      }
    } catch (e) {}
    return Promise.reject(error);
  }
);

export default api;