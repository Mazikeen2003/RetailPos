import { useState } from "react";
import NoticeBanner from "../components/NoticeBanner";

export default function LoginPage({ error, loading, onSubmit }) {
  const [form, setForm] = useState({
    email: "",
    password: "",
  });

  const handleSubmit = (event) => {
    event.preventDefault();
    onSubmit(form);
  };

  return (
    <div className="auth-shell">
      <div className="auth-panel">
        <div>
          <p className="eyebrow">RetailPOS</p>
          <h1 className="auth-title">Welcome back</h1>
          <p className="auth-copy">Please sign in to continue.</p>
        </div>

        <NoticeBanner tone="danger" message={error} />

        <form className="auth-form" onSubmit={handleSubmit}>
          <label className="field">
            <span>Email</span>
            <input
              type="email"
              value={form.email}
              onChange={(event) => setForm((current) => ({ ...current, email: event.target.value }))}
              placeholder="cashier@test.com"
            />
          </label>
          <label className="field">
            <span>Password</span>
            <input
              type="password"
              value={form.password}
              onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
              placeholder="password123"
            />
          </label>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? "Signing in..." : "Login"}
          </button>
        </form>
      </div>
    </div>
  );
}
