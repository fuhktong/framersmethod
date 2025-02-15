import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./hamilton.css";

const Hamilton = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers&#39; Method: Electoral College & Hamilton Method
            Explained - The Hamilton Method
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="The Hamilton Method will give the United States multiple parties, decentralize our elections, and prevent national populist rhetoric."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, hamilton, president, constitution, multiple parties, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="language" content="English" />
          <meta name="robots" content="index, follow" />
          <meta http-equiv="Content-Type" content="text/html"></meta>
          {/* Open Graph Tags */}
          <meta property="og:type" content="website" />
          <meta property="og:url" content="https://www.framersmethod.com/" />
          <meta property="og:site_name" content="The Framers&#39; Method" />
          <meta
            property="og:title"
            content="The Framers&#39; Method - The Hamilton Method"
          />
          <meta
            property="og:description"
            content="The Hamilton Method will give the United States multiple parties, decentralize our elections, and prevent national populist rhetoric."
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
            content="The Framers&#39; Method - The Hamilton Method"
          />
          <meta
            name="twitter:description"
            content="The Hamilton Method will give the United States multiple parties, decentralize our elections, and prevent national populist rhetoric."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div className="hamilton-logo">
          <img src="../hamiltonmethodlogo2.png" alt="The Hamilton Method" />
        </div>
        <SocialMediaBar />
        <section className="hamilton-template">
          <div className="hamilton-template-text">
            <h2>How it works</h2>
            <h1>Proportional Representation with a Touch of Competition</h1>
            <p>
              The Hamilton Method splits up electors by first giving each party
              the whole number of reps they should get based on the votes they
              received. Then, any leftover seats go to the party or parties with
              the largest remainder.
            </p>
          </div>
          <div className="hamilton-template-img">
            <img
              src="./hamiltonmethodequation.png"
              alt="The Hamilton Method Equation"
            />
          </div>
        </section>
        <section
          className="hamilton-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="hamilton-template-text">
            <h2>The Hamilton Method</h2>
            <h1>
              A decentralized election system will prevent national populist
              rhetoric.
            </h1>
            <p>
              With thousands of possible electors, political influence is
              dispersed throughout the country. No elector candidate will wield
              concentrated power on the national stage. Money in politics, media
              influence, and foreign intelligence services will have little
              influence on the electoral process.
            </p>
          </div>
          <div className="hamilton-template-img">
            <img
              src="../hamiltonmethodnobackground.png"
              alt="The Hamilton Method"
            />
          </div>
        </section>
        <section className="hamilton-template">
          <div className="hamilton-template-text">
            <h1>Key Points of the Hamilton Method:</h1>
            <p>• Elections are local within each state</p>
            <p>• The national election is eliminated</p>
            <p>• Populism and tyranny are ineffective</p>
            <p>
              • Money is still used for campaigning, but will be decentralized
            </p>
            <p>
              • Thousands of potential electors prevent influence by foreign
              intelligence services as well as traditional media and social
              media
            </p>
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default Hamilton;
