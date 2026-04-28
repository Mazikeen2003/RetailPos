import api from '../api/axios';

export async function createSale(payload) {
  const res = await api.post('/sales', payload);
  return res.data;
}

export async function cancelSale(payload) {
  const res = await api.post('/sales/cancel', payload);
  return res.data;
}

export async function voidItem(payload) {
  const res = await api.post('/sales/void-item', payload);
  return res.data;
}

export async function postVoid(payload) {
  const res = await api.post('/sales/post-void', payload);
  return res.data;
}

export async function voidSale(id, reason) {
  const res = await api.post(`/sales/${id}/void`, { reason });
  return res.data;
}

export async function getReceipt(id) {
  const res = await api.get(`/receipts/${id}`);
  return res.data;
}

export async function reprintReceipt(id) {
  const res = await api.post(`/receipts/${id}/reprint`);
  return res.data;
}
