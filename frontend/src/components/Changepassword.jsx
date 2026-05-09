import { useState } from "react";
import "../styles/modal.css";

export default function ChangePassword({ onClose }) {
  const [senhaAtual, setSenhaAtual] = useState("");
  const [novaSenha, setNovaSenha] = useState("");
  const [msg, setMsg] = useState("");
  const [loading, setLoading] = useState(false);

  const handleChange = async (e) => {
    e.preventDefault();

    if (loading) return;
    setLoading(true);
    setMsg("");

    try {
      const res = await fetch("http://localhost:8000/change-password.php", {
        method: "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          senhaAtual,
          novaSenha,
        }),
      });

      const data = await res.json().catch(() => null);

      if (!data) {
        setMsg("Erro inesperado no servidor");
        return;
      }

      if (!res.ok) {
        setMsg(data.error || "Erro ao alterar senha");
        return;
      }

      setMsg("Senha alterada com sucesso");

      setSenhaAtual("");
      setNovaSenha("");

      setTimeout(() => {
        if (data.force_logout) {
          onClose?.();
          window.location.href = "/";
        }
      }, 800);

    } catch {
      setMsg("Erro de conexão");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="modal-overlay" onClick={onClose}>
      <div className="modal" onClick={(e) => e.stopPropagation()}>
        <h3>Alterar senha</h3>

        <form onSubmit={handleChange}>
          <input
            type="password"
            placeholder="Senha atual"
            value={senhaAtual}
            onChange={(e) => setSenhaAtual(e.target.value)}
          />

          <input
            type="password"
            placeholder="Nova senha"
            value={novaSenha}
            onChange={(e) => setNovaSenha(e.target.value)}
          />

          <button type="submit" disabled={loading}>
            {loading ? "Alterando..." : "Alterar senha"}
          </button>
        </form>

        {msg && <p>{msg}</p>}

        <button className="close" onClick={onClose}>
          Fechar
        </button>
      </div>
    </div>
  );
}