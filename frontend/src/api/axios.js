import axios from "axios";

const TOKEN_KEY = "retailpos.token";

const api = axios.create({
  baseURL:
    process.env.REACT_APP_API_BASE_URL ||
    process.env.REACT_APP_API_URL ||
    process.env.REACT_APP_API_BASE ||
    "http://127.0.0.1:8000/api",
});

api.interceptors.request.use((config) => {
  const token = localStorage.getItem(TOKEN_KEY);

  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  config.headers.Accept = "application/json";

  return config;
});

export function setAuthToken(token) {
  if (token) {
    localStorage.setItem(TOKEN_KEY, token);
    api.defaults.headers.common.Authorization = `Bearer ${token}`;
    return;
  }

  localStorage.removeItem(TOKEN_KEY);
  delete api.defaults.headers.common.Authorization;
}

export default api;
