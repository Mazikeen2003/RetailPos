import { useEffect, useState } from "react";
import "./App.css";
import { getProfile, login, logout } from "./services/authService";
import LoginPage from "./pages/LoginPage";
import PosDashboardPage from "./pages/PosDashboardPage";

const TOKEN_KEY = "retailpos.token";
const USER_KEY = "retailpos.user";

function readStoredUser() {
  const rawUser = localStorage.getItem(USER_KEY);

  if (!rawUser) {
    return null;
  }

  try {
    return JSON.parse(rawUser);
  } catch {
    localStorage.removeItem(USER_KEY);
    return null;
  }
}

export default function App() {
  const [token, setToken] = useState(() => localStorage.getItem(TOKEN_KEY) || "");
  const [user, setUser] = useState(() => readStoredUser());
  const [bootstrapping, setBootstrapping] = useState(Boolean(localStorage.getItem(TOKEN_KEY)));
  const [authLoading, setAuthLoading] = useState(false);
  const [authError, setAuthError] = useState("");

  useEffect(() => {
    if (!token) {
      setBootstrapping(false);
      return;
    }

    let active = true;

    getProfile()
      .then((profile) => {
        if (!active) {
          return;
        }

        setUser(profile);
        localStorage.setItem(USER_KEY, JSON.stringify(profile));
      })
      .catch(() => {
        if (!active) {
          return;
        }

        localStorage.removeItem(TOKEN_KEY);
        localStorage.removeItem(USER_KEY);
        setToken("");
        setUser(null);
      })
      .finally(() => {
        if (active) {
          setBootstrapping(false);
        }
      });

    return () => {
      active = false;
    };
  }, [token]);

  const handleLogin = async (credentials) => {
    setAuthLoading(true);
    setAuthError("");

    try {
      const payload = await login(credentials);
      localStorage.setItem(TOKEN_KEY, payload.token);
      localStorage.setItem(USER_KEY, JSON.stringify(payload.user));
      setToken(payload.token);
      setUser(payload.user);
    } catch (error) {
      setAuthError(error.response?.data?.message || "Login failed. Check your credentials.");
    } finally {
      setAuthLoading(false);
    }
  };

  const handleLogout = async () => {
    try {
      await logout();
    } catch {
      // Ignore logout transport failures and clear local auth anyway.
    }

    localStorage.removeItem(TOKEN_KEY);
    localStorage.removeItem(USER_KEY);
    setToken("");
    setUser(null);
  };

  if (bootstrapping) {
    return (
      <div className="app-state-screen">
        <div className="state-card">
          <p className="eyebrow">RetailPOS</p>
          <h1>Restoring your session</h1>
          <p>Checking your access token and loading the latest profile.</p>
        </div>
      </div>
    );
  }

  if (!token || !user) {
    return <LoginPage error={authError} loading={authLoading} onSubmit={handleLogin} />;
  }

  return <PosDashboardPage user={user} onLogout={handleLogout} />;
}
