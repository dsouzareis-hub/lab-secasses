import { useEffect, useState } from "react";
import { useNavigate } from "react-router-dom";
import Navbar from "../components/Navbar";
import Footer from "../components/Footer";
import "../styles/index.css";
import "../styles/navbar.css";
import "../styles/footer.css";

export default function Home() {
  const [itens, setItens] = useState([]);
  const [nome, setNome] = useState("");
  const [descricao, setDescricao] = useState("");
  const [editandoId, setEditandoId] = useState(null);
  const [loading, setLoading] = useState(true);

  const navigate = useNavigate();

  useEffect(() => {
    const checkAuth = async () => {
      try {
        const res = await fetch("http://localhost:8000/me.php", {
          method: "GET",
          credentials: "include",
        });

        const data = await res.json().catch(() => null);

        if (!data || !data.ok) {
          navigate("/");
          return;
        }

        fetchItens();
      } catch {
        navigate("/");
      } finally {
        setLoading(false);
      }
    };

    checkAuth();
  }, [navigate]);

  const fetchItens = async () => {
    try {
      const res = await fetch("http://localhost:8000/itens.php", {
        method: "GET",
        credentials: "include",
      });

      const data = await res.json().catch(() => null);

      if (!data) return;

      setItens(data.data || []);
    } catch {}
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const res = await fetch("http://localhost:8000/itens.php", {
        method: editandoId ? "PUT" : "POST",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          id: editandoId,
          nome,
          descricao,
        }),
      });

      const data = await res.json().catch(() => null);

      if (!data) return;

      setEditandoId(null);
      setNome("");
      setDescricao("");

      fetchItens();
    } catch {}
  };

  const handleEdit = (item) => {
    setNome(item.nome);
    setDescricao(item.descricao);
    setEditandoId(item.id);
  };

  const handleDelete = async (id) => {
    try {
      const res = await fetch("http://localhost:8000/itens.php", {
        method: "DELETE",
        credentials: "include",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ id }),
      });

      const data = await res.json().catch(() => null);

      if (!data) return;

      fetchItens();
    } catch {}
  };

  if (loading) return <p>Carregando...</p>;

  return (
    <>
      <Navbar />

      <div className="container">
        <h2>Gerenciamento de Itens</h2>

        <form onSubmit={handleSubmit}>
          <input
            type="text"
            placeholder="Nome"
            value={nome}
            onChange={(e) => setNome(e.target.value)}
          />

          <input
            type="text"
            placeholder="Descrição"
            value={descricao}
            onChange={(e) => setDescricao(e.target.value)}
          />

          <button type="submit">
            {editandoId ? "Atualizar" : "Cadastrar"}
          </button>
        </form>

        <ul className="lista">
          {itens.map((item) => (
            <li key={item.id}>
              <strong>{item.nome}</strong> - {item.descricao}

              <div>
                <button onClick={() => handleEdit(item)}>Editar</button>
                <button onClick={() => handleDelete(item.id)}>Excluir</button>
              </div>
            </li>
          ))}
        </ul>
      </div>

      <Footer />
    </>
  );
}