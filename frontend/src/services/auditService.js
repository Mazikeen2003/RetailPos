import api from "../api/axios";

export async function createAuditLog(payload) {
  const response = await api.post("/audit-logs", payload);
  return response.data;
}

export async function getAuditLogs() {
  const response = await api.get("/audit-logs");
  return response.data;
}
