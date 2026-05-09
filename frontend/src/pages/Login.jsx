import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";
import "../styles/index.css";
import "../styles/navbar.css";
import "../styles/footer.css";

export default function Login() {
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [erro, setErro] = useState("");
  const [loading, setLoading] = useState(false);
  const [bloqueadoTempo, setBloqueadoTempo] = useState(0);

  const navigate = useNavigate();

  useEffect(() => {
    if (bloqueadoTempo <= 0) return;

    const interval = setInterval(() => {
      setBloqueadoTempo((prev) => prev - 1);
    }, 1000);

    return () => clearInterval(interval);
  }, [bloqueadoTempo]);

  useEffect(() => {
    if (bloqueadoTempo === 0) setErro("");
  }, [bloqueadoTempo]);

  const handleLogin = async (e) => {
    e.preventDefault();

    if (loading || bloqueadoTempo > 0) return;

    setLoading(true);
    setErro("");

    try {
      if (!email || !senha) {
        setErro("Preencha todos os campos");
        return;
      }

      const response = await fetch("http://localhost:8000/login.php", {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email.trim().toLowerCase(),
          senha: senha.trim(),
        }),
      });

      const text = await response.text();

      let data;
      try {
        data = JSON.parse(text);
      } catch {
        setErro("Erro inesperado no servidor");
        return;
      }

      if (response.status === 429) {
        setErro(data.error || "Muitas tentativas");

        if (data.retry_after) {
          setBloqueadoTempo(data.retry_after);
        }

        return;
      }

      if (!response.ok) {
        setErro(data.error || "Erro ao fazer login");
        return;
      }

      navigate("/home");
    } catch {
      setErro("Erro inesperado");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Navbar />

      <div className="container">
        <h2>Login</h2>

        <form onSubmit={handleLogin}>
          <input
            type="email"
            placeholder="Email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
          />

          <input
            type="password"
            placeholder="Senha"
            value={senha}
            onChange={(e) => setSenha(e.target.value)}
          />

          <button disabled={loading || bloqueadoTempo > 0}>
            {bloqueadoTempo > 0
              ? `Aguarde ${bloqueadoTempo}s`
              : loading
              ? "Entrando..."
              : "Entrar"}
          </button>
        </form>

        {erro && <p className="erro">{erro}</p>}
      </div>

      <Footer />
    </>
  );
}