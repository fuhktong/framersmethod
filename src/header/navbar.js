// NavBar.js
import React from "react";
import { Link } from "react-router-dom";
import "./navbar.css";

const NavBar = () => {
  return (
    <nav className="desktop-nav">
      <ul>
        <li>
          <Link to="/general-caucus">
            <span className="first-word">GENERAL </span>
            <span className="second-word">CAUCUS</span>
          </Link>
        </li>
        <li>
          <div id="navbar-electoral-college">
            <span className="first-word">ELECTORAL </span>
            <span className="second-word">COLLEGE</span>
          </div>
          <div className="dropdown-content">
            <Link to="/democracy-vs-republic">DEMOCRACY vs REPUBLIC</Link>
            <Link to="/national-elections">NATIONAL ELECTIONS</Link>
            <Link to="/hamilton-method">HAMILTON METHOD</Link>
            <Link to="/electors-convention">ELECTORS' CONVENTION</Link>
          </div>
        </li>
        <li>
          <Link to="/book">BOOK</Link>
        </li>
        <li>
          <Link to="/faq">FAQ</Link>
        </li>
        <li>
          <Link to="/contribute">CONTRIBUTE</Link>
        </li>
        <li>
          <div id="navbar-electoral-college">CONTACT</div>
          <div className="dropdown-content">
            <Link to="/team">TEAM</Link>
            <Link to="/contact">CONTACT</Link>
          </div>
        </li>
      </ul>
    </nav>
  );
};

export default NavBar;
