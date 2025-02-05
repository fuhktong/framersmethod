import React, { useState } from "react";
import "./header.css";
import Logo from "./logo.js";
import NavBar from "./navbar";
import Toggle from "./toggle.js";

const Header = () => {
  const [menuOpen, setMenuOpen] = useState(false);

  const toggleMenu = () => {
    setMenuOpen(!menuOpen);
  };

  return (
    <header>
      <Logo />
      <div>
        <NavBar />
        <Toggle isOpen={menuOpen} toggleMenu={toggleMenu} />
      </div>
    </header>
  );
};

export default Header;
