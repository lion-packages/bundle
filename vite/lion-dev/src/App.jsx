import "./assets/index.css"

import { Routes, Route } from "react-router-dom"
import NavbarNavigation from "./pages/components/NavbarNavigation"
import RoutesWeb from "./pages/RoutesWeb"
import Login from "./pages/Login"
import Register from "./pages/Register"
import NotFound from "./pages/NotFound"
import Home from "./pages/Home"

export default function App() {
  return (
    <div className="bg-dark">
      <NavbarNavigation />

      <Routes>
        <Route path="*" element={<NotFound />} />
        <Route path="/" element={<Home />} />

        <Route path="lion">
          <Route path="routes" element={<RoutesWeb />} />
        </Route>

        <Route path="auth">
          <Route path="login" element={<Login />} />
          <Route path="register" element={<Register />} />
        </Route>
      </Routes>
    </div>
  )
}
