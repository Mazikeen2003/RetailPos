import api from "../api/axios";

export async function getSales() {
  const response = await api.get("/sales");
  return response.data;
}

export async function createSale(payload) {
  const response = await api.post("/sales", payload);
  return response.data;
}
