import api from "../api/axios";

export async function getProducts(search = "") {
  const response = await api.get("/products", {
    params: search ? { search } : undefined,
  });

  return response.data;
}

export async function createProduct(payload) {
  const response = await api.post("/products", payload);
  return response.data;
}

export async function updateProduct(id, payload) {
  const response = await api.patch(`/products/${id}`, payload);
  return response.data;
}
