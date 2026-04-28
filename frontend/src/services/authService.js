import api from "../api/axios";

export async function login(credentials) {
  const response = await api.post("/login", credentials);
  return response.data;
}

export async function getProfile() {
  const response = await api.get("/profile");
  return response.data;
}

export async function logout() {
  const response = await api.post("/logout");
  return response.data;
}
