import { useEffect, useState } from "react";
import api from "./api/axios";

function App() {
  const [message, setMessage] = useState("");

  useEffect(() => {
    api.get("/test").then(res => {
      setMessage(res.data.message);
    });
  }, []);

  return (
    <div>
      <h1>Retail POS System</h1>
      <p>{message || "Loading..."}</p>
    </div>
  );
}

export default App;