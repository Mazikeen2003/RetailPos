import api, { setAuthToken } from '../api/axios';

export async function login(nameOrEmail, password) {
  const payload = { password };
  // prefer email if contains @ else name
  if (nameOrEmail && nameOrEmail.includes('@')) payload.email = nameOrEmail;
  else payload.name = nameOrEmail;

  const res = await api.post('/login', payload);
  const token = res.data.token;
  setAuthToken(token);
  return res.data;
}

export async function logout() {
  await api.post('/logout');
  setAuthToken(null);
}
