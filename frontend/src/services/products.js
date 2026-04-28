import api from '../api/axios';

export async function fetchProducts(q = null) {
  const params = q ? { q } : {};
  const res = await api.get('/products', { params });
  return res.data;
}

export async function fetchProductByBarcode(barcode) {
  const res = await api.get(`/products/barcode/${encodeURIComponent(barcode)}`);
  return res.data;
}

export async function createProduct(data) {
  const res = await api.post('/products', data);
  return res.data;
}

export async function updateProduct(id, data) {
  const res = await api.put(`/products/${id}`, data);
  return res.data;
}

export async function deactivateProduct(id) {
  const res = await api.delete(`/products/${id}`);
  return res.data;
}
