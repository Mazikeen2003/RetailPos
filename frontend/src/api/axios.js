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

export default api;