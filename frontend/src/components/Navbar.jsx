import "../styles/navbar.css";
import { useNavigate } from "react-router-dom";
import { useEffect, useState } from "react";
import logo from "../images/logo_general_eletric.png";
import ChangePassword from "./ChangePassword";

export default function Navbar() {
  const navigate = useNavigate();
  const [session, setSession] = useState(null);
  const [loading, setLoading] = useState(true);
  const [showModal, setShowModal] = useState(false);

  const checkSession = async () => {
    try {
      const res = await fetch("http://localhost:8000/me.php", {
        credentials: "include",
      });

      const data = await res.json().catch(() => null);

      if (!res.ok || !data?.ok) {
        setSession(null);
        return;
      }

      setSession(data.user || null);
    } catch {
      setSession(null);
    } finally {
      setLoading(false);
    }
  };

  useEffect(() => {
    checkSession();
  }, []);

  const handleLogout = async () => {
    try {
      await fetch("http://localhost:8000/logout.php", {
        method: "POST",
        credentials: "include",
      });

      setSession(null);
      navigate("/");
    } catch {}
  };

  if (loading) return null;

  return (
    <>
      <nav className="navbar">
        <div
          className="navbar-left"
          onClick={() => navigate(session ? "/home" : "/")}
        >
          <img src={logo} alt="Logo SENAI" className="logo" />
        </div>

        <div className="navbar-right">
          {session ? (
            <>
              <button onClick={() => navigate("/home")}>Home</button>

              <button onClick={() => setShowModal(true)}>
                Alterar Senha
              </button>

              <button className="logout" onClick={handleLogout}>
                Logout
              </button>
            </>
          ) : (
            <>
              <button onClick={() => navigate("/")}>Login</button>
              <button onClick={() => navigate("/cadastro")}>
                Cadastro
              </button>
            </>
          )}
        </div>
      </nav>

      {showModal && (
        <ChangePassword onClose={() => setShowModal(false)} />
      )}
    </>
  );
}