import { useEffect, useState } from "react";
import { Navigate } from "react-router-dom";

export default function PrivateRoute({ children }) {
  const [auth, setAuth] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    async function checkAuth() {
      try {
        const res = await fetch("http://localhost:8000/me.php", {
          credentials: "include",
        });

        if (!res.ok) {
          setAuth(false);
          return;
        }

        // 🔥 evita quebra se backend retornar erro/HTML
        const text = await res.text();

        try {
          const data = JSON.parse(text);
          setAuth(!!data.user);
        } catch (e) {
          console.error("Resposta inválida do backend:", text);
          setAuth(false);
        }

      } catch (err) {
        console.error("Erro ao verificar auth:", err);
        setAuth(false);
      } finally {
        setLoading(false);
      }
    }

    checkAuth();
  }, []);

  if (loading) return <p>Carregando...</p>;

  if (!auth) {
    return <Navigate to="/" replace />;
  }

  return children;
}