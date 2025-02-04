import React from "react";
import { SocialMediaBar } from "../socialmediabar.js";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./contribute.css";

const Contribute = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers' Method: Electoral College & Hamilton Method Explained -
            Contribute
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="Contribute to the Framers' Method by supporting us on Patreon or buying one of our t-shirts. Help defeat populism and tyrannically-minded politicians."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution, patreon, printify, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="language" content="English" />
          <meta name="robots" content="index, follow" />
          {/* Open Graph Tags */}
          <meta property="og:type" content="website" />
          <meta
            property="og:url"
            content="https://www.framersmethod.com/contribute"
          />
          <meta property="og:site_name" content="The Framers' Method" />
          <meta
            property="og:title"
            content="The Framers' Method - Contribute"
          />
          <meta
            property="og:description"
            content="Contribute to the Framers' Method by supporting us on Patreon or buying one of our t-shirts. Help defeat populism and tyrannically-minded politicians."
          />
          <meta
            property="og:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
          <meta property="og:image:width" content="1200" />
          <meta property="og:image:height" content="630" />
          {/* Twitter Tags */}
          <meta name="twitter:card" content="summary_large_image" />
          <meta name="twitter:site" content="@framersmethod" />
          <meta
            name="twitter:title"
            content="The Framers' Method - Contribute"
          />
          <meta
            name="twitter:description"
            content="Contribute to the Framers' Method by supporting us on Patreon or buying one of our t-shirts. Help defeat populism and tyrannically-minded politicians."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div className="contribute-logo">
          <img src="framersmethod.png" alt="The Framers' Method" />
        </div>
        <div className="contribute-logo-text">
          <h2>Restore the American Republic!</h2>
          <p>
            Contribute to the The Framers’ Method and <br />
            help us defeat Populism and Tyranny.
          </p>
        </div>
        <SocialMediaBar />
        <section className="contribute">
          <div className="contribute-text">
            <h1>Support us on Patreon</h1>
            <p>
              The best way to support the Framers' Method <br />
              is through a subscription on Patreon.
            </p>
            <a
              className="contribute-button"
              href="https://www.patreon.com/framersmethod"
              target="_blank"
              rel="noreferrer"
            >
              <button class="btn-tshirt">Support Here</button>
            </a>
          </div>
          <div class="contribute-img">
            <img src="patreon.png" alt="The Framers' Method on Patreon" />
          </div>
        </section>
        <div class="contribute-merch">The Framers' Method - Merch</div>
        <section className="contribute">
          <div className="contribute-text">
            <h1>Short Sleeve Shirt</h1>
            <a
              href="https://framersmethod.printify.me/product/3613867/the-framers-method-unisex-jersey-short-sleeve-tee"
              target="_blank"
              rel="noreferrer"
            >
              <button className="btn-tshirt">Buy Here</button>
            </a>
          </div>
          <div class="contribute-img">
            <a
              href="https://framersmethod.printify.me/product/3613867/the-framers-method-unisex-jersey-short-sleeve-tee"
              target="_blank"
              rel="noreferrer"
            >
              <img
                src="framersshortsleeve.png"
                alt="The Framers' Method - Short Sleeve"
              />
            </a>
          </div>
        </section>
        <section className="contribute" style={{ backgroundColor: "#f5f4f0" }}>
          <div className="contribute-text">
            <h1>V-Neck Shirt</h1>
            <a
              href="https://framersmethod.printify.me/product/7705494/the-framers-method-unisex-jersey-short-sleeve-v-neck-tee"
              target="_blank"
              rel="noreferrer"
            >
              <button class="btn-tshirt">Buy Here</button>
            </a>
          </div>
          <div class="contribute-img">
            <a
              href="https://framersmethod.printify.me/product/7705494/the-framers-method-unisex-jersey-short-sleeve-v-neck-tee"
              target="_blank"
              rel="noreferrer"
            >
              <img src="framersvneck.png" alt="The Framers' Method - Vneck" />
            </a>
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default Contribute;
