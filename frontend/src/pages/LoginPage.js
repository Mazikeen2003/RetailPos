import { useState } from "react";
import NoticeBanner from "../components/NoticeBanner";

const demoUsers = [
  { name: "Maria Cruz", role: "Cashier", username: "Maria Cruz" },
  { name: "Daniel Reyes", role: "Supervisor", username: "Daniel Reyes" },
  { name: "Angela Santos", role: "Administrator", username: "Angela Santos" },
];

export default function LoginPage({ darkMode, error, loading, onSubmit, onToggleDark }) {
  const [form, setForm] = useState({
    username: "",
    password: "",
  });

  const handleSubmit = (event) => {
    event.preventDefault();
    const identifier = form.username.trim();

    onSubmit({
      [identifier.includes("@") ? "email" : "username"]: identifier,
      password: form.password,
    });
  };

  const fillDemoUser = (demoUser) => {
    setForm({
      username: demoUser.username,
      password: "1234",
    });
  };

  return (
    <div className="auth-shell">
      <div className="auth-panel auth-panel-pro">
        <div className="auth-panel-header">
          <div>
            <h1 className="auth-title">Sign In</h1>
            <p className="auth-copy">Welcome back to RetailPOS Pro</p>
          </div>
          <button type="button" className="theme-toggle" onClick={onToggleDark}>
            {darkMode ? "Dark" : "Light"}
          </button>
        </div>

        <NoticeBanner tone="danger" message={error} />

        <form className="auth-form" onSubmit={handleSubmit}>
          <label className="field">
            <span>Username</span>
            <input
              autoComplete="username"
              type="text"
              value={form.username}
              onChange={(event) => setForm((current) => ({ ...current, username: event.target.value }))}
              placeholder="Enter your username"
            />
          </label>
          <label className="field">
            <span>Password</span>
            <input
              autoComplete="current-password"
              type="password"
              value={form.password}
              onChange={(event) => setForm((current) => ({ ...current, password: event.target.value }))}
              placeholder="Enter password"
            />
          </label>
          <button type="submit" className="btn btn-primary" disabled={loading}>
            {loading ? "Signing in..." : "Login"}
          </button>
        </form>

        <div className="quick-access">
          <div className="quick-access-rule">
            <span />
            <strong>Quick Access</strong>
            <span />
          </div>

          <p className="demo-label">Demo Users</p>

          <div className="demo-grid">
            {demoUsers.map((demoUser) => (
              <button
                type="button"
                className="demo-user-card"
                key={demoUser.username}
                onClick={() => fillDemoUser(demoUser)}
              >
                <span className="demo-avatar">{demoUser.name.slice(0, 1)}</span>
                <span>
                  <strong>{demoUser.name}</strong>
                  <small>{demoUser.role}</small>
                </span>
              </button>
            ))}
          </div>

          <div className="demo-password-note">
            <strong>Use password</strong>
            <span>1234 for all demo accounts</span>
          </div>
        </div>
      </div>
    </div>
  );
}
