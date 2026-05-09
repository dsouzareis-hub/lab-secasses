import { useState, useEffect } from "react";
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";
import "../styles/index.css";
import "../styles/navbar.css";
import "../styles/footer.css";

export default function Cadastro() {
  const [nome, setNome] = useState("");
  const [email, setEmail] = useState("");
  const [senha, setSenha] = useState("");
  const [loading, setLoading] = useState(false);
  const [msg, setMsg] = useState("");

  const navigate = useNavigate();

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const res = await fetch("http://localhost:8000/me.php", {
          credentials: "include",
        });

        const data = await res.json().catch(() => null);

        if (res.ok && data?.ok) {
          navigate("/home");
        }
      } catch {}
    };

    checkAuth();
  }, [navigate]);

  const handleCadastro = async (e) => {
    e.preventDefault();

    if (loading) return;

    setLoading(true);
    setMsg("");

    try {
      if (!nome || !email || !senha) {
        setMsg("Preencha todos os campos do forms");
        return;
      }

      if (senha.length < 8) {
        setMsg("Senha deve ter no mínimo 8 caracteres");
        return;
      }

      const response = await fetch("http://localhost:8000/cadastro.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          nome,
          email,
          senha,
        }),
      });

      const text = await response.text();

      let data;

      try {
        data = JSON.parse(text);
      } catch {
        setMsg("Erro no servidor");
        return;
      }

      if (!response.ok) {
        setMsg(data.error || "Erro ao cadastrar");
        return;
      }

      setMsg("Cadastro realizado com sucesso");

      setTimeout(() => {
        navigate("/");
      }, 800);

    } catch {
      setMsg("Erro inesperado");
    } finally {
      setLoading(false);
    }
  };

  return (
    <>
      <Navbar />

      <div className="container">
        <h2>Cadastro</h2>

        <form onSubmit={handleCadastro}>
          <input
            type="text"
            placeholder="Nome"
            value={nome}
            onChange={(e) => setNome(e.target.value)}
          />

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

          <button type="submit" disabled={loading}>
            {loading ? "Cadastrando..." : "Cadastrar"}
          </button>

          {msg && <p>{msg}</p>}

          <p className="link-login">
            Já possui conta?{" "}
            <span onClick={() => navigate("/")}>
              Fazer login
            </span>
          </p>
        </form>
      </div>

      <Footer />
    </>
  );
}