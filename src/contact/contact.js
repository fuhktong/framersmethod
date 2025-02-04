import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import ContactForm from "./contactform.jsx";
import "./contact.css";

const Contact = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          <title>
            The Framers' Method: Electoral College & Hamilton Method Explained -
            Contact
          </title>
          <meta
            name="description"
            content="Send a message to the Framers' Method. The American republic is under threat from populism and tyrannical-minded politicians. The Framers' Method can restore political stability to government and the American people."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="robots" content="index, follow" />
          <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
          <meta name="language" content="English" />

          <meta property="og:type" content="website" />
          <meta
            property="og:url"
            content="https://www.framersmethod.com/contact"
          />
          <meta property="og:site_name" content="The Framers' Method" />
          <meta property="og:title" content="The Framers' Method - Contact" />
          <meta
            property="og:description"
            content="Send a message to the Framers' Method. The American republic is under threat from populism and tyrannical-minded politicians. The Framers' Method can restore political stability to government and the American people."
          />
          <meta
            property="og:image"
            content="https://www.framersmethod.com/framersmethod-withbackground.png"
          />
          <meta property="og:image:width" content="1200" />
          <meta property="og:image:height" content="630" />

          <meta name="twitter:site" content="@framersmethod" />
          <meta name="twitter:title" content="The Framers' Method - Contact" />
          <meta
            name="twitter:description"
            content="Send a message to the Framers' Method. The American republic is under threat from populism and tyrannical-minded politicians. The Framers' Method can restore political stability to government and the American people."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethod-withbackground.png"
          />
          <meta name="twitter:card" content="summary_large_image" />
        </Helmet>
        <div className="home-logo">
          <img src="./framersmethod.png" alt="The Framers' Method" />
        </div>
        <SocialMediaBar />
        <ContactForm />
      </section>
    </PageTransition>
  );
};

export default Contact;
