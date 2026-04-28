import api from "../api/axios";

export async function getUsers() {
  const response = await api.get("/users");
  return response.data;
}

export async function getUserMeta() {
  const response = await api.get("/users/meta");
  return response.data;
}

export async function createUser(payload) {
  const response = await api.post("/users", payload);
  return response.data;
}

export async function updateUser(id, payload) {
  const response = await api.patch(`/users/${id}`, payload);
  return response.data;
}
