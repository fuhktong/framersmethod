import { Route, Routes, useLocation } from "react-router-dom";
import { HelmetProvider } from "react-helmet-async";
import ScrollToTop from "./scrolltotop.js";
import Header from "./header/header.js";
import Footer from "./footer/Footer.js";
import Home from "./home/home.js";
import GeneralCaucus from "./generalcaucus/generalcaucus.js";
import NationalElections from "./nationalelections/nationalelections.js";
import DemVsRep from "./demvsrep/demvsrep.js";
import Hamilton from "./hamilton/hamilton.js";
import ElectorsConvention from "./electorsconvention/electorsconvention.js";
import Book from "./book/book.js";
import FAQ from "./faq/faq.js";
import Contribute from "./contribute/contribute.js";
import Team from "./team/team.js";
import Contact from "./contact/contact.js";
import "./global.css";

function App() {
  const location = useLocation();

  return (
    <HelmetProvider>
      <div className="app">
        <ScrollToTop />
        <Header />
        <Routes location={location}>
          <Route path="/" element={<Home />} />
          <Route path="general-caucus" element={<GeneralCaucus />} />
          <Route path="national-elections" element={<NationalElections />} />
          <Route path="democracy-vs-republic" element={<DemVsRep />} />
          <Route path="hamilton-method" element={<Hamilton />} />
          <Route path="electors-convention" element={<ElectorsConvention />} />
          <Route path="book" element={<Book />} />
          <Route path="faq" element={<FAQ />} />
          <Route path="contribute" element={<Contribute />} />
          <Route path="team" element={<Team />} />
          <Route path="contact" element={<Contact />} />
          <Route component={<Home />} />
        </Routes>
        <Footer />
      </div>
    </HelmetProvider>
  );
}

export default App;
