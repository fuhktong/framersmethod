import React from "react";
import { Link } from "react-router-dom";
import "./toggle.css";

const Toggle = ({ isOpen, toggleMenu }) => {
  return (
    <div className="mobile-nav">
      <button
        className={`hamburger ${isOpen ? "open" : ""}`}
        onClick={toggleMenu}
      >
        <span></span>
        <span></span>
        <span></span>
      </button>

      <div className={`mobile-menu ${isOpen ? "open" : ""}`}>
        <ul>
          <li>
            <Link to="/general-caucus" onClick={toggleMenu}>
              GENERAL CAUCUS
            </Link>
          </li>
          <li>
            <Link to="/how-it-works" onClick={toggleMenu}>
              HOW THE GENERAL CAUCUS WORKS
            </Link>
          </li>
          <li>
            <Link to="/democracy-vs-republic" onClick={toggleMenu}>
              DEMOCRACY vs REPUBLIC
            </Link>
          </li>
          <li>
            <Link to="/hamilton-method" onClick={toggleMenu}>
              HAMILTON METHOD
            </Link>
          </li>
          <li>
            <Link to="/electors-convention" onClick={toggleMenu}>
              ELECTORS' CONVENTION
            </Link>
          </li>
          <li>
            <Link to="/book" onClick={toggleMenu}>
              BOOK
            </Link>
          </li>
          <li>
            <Link to="/faq" onClick={toggleMenu}>
              FAQ
            </Link>
          </li>
          <li>
            <Link to="/contribute" onClick={toggleMenu}>
              CONTRIBUTE
            </Link>
          </li>
          <li>
            <Link to="/team" onClick={toggleMenu}>
              TEAM
            </Link>
          </li>
          <li>
            <Link to="/contact" onClick={toggleMenu}>
              CONTACT
            </Link>
          </li>
        </ul>
      </div>
    </div>
  );
};

export default Toggle;
