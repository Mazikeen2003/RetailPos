import api from '../api/axios';

export async function fetchAuditLogs(q = null) {
  const params = q ? { q } : {};
  const res = await api.get('/audit-logs', { params });
  return res.data;
}
