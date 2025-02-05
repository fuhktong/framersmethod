import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./electorsconvention.css";

const ElectorsConvention = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers&#39; Method: Electoral College & Hamilton Method
            Explained - Electors' Convention
          </title>
          <meta charset="utf-8" />
          <meta
            name="description"
            content="An electors' convention will give America the president it needs for the future."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution, convention, electors convention"
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
            content="The Framers&#39; Method - Electors' Convention"
          />
          <meta
            property="og:description"
            content="An electors' convention will give America the president it needs for the future."
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
            content="The Framers&#39; Method - Electors' Convention"
          />
          <meta
            name="twitter:description"
            content="An electors' convention will give America the president it needs for the future."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
          />
        </Helmet>
        <div class="electors-logo">
          <img
            src="./electorsconventionlogo3.png"
            alt="An Electors' Convention"
          />
        </div>
        <SocialMediaBar />
        <section className="electors-template">
          <div className="electors-template-text">
            <h1>An Electors' Convention</h1>
            <h2>
              Deliberation will give America the president it needs for the
              future.{" "}
            </h2>
            <p>
              After the several states choose their electors, an electors'
              convention will choose the next president. In this environment,
              populism and tyranny are impossible. To become the next president
              one of the electors will need the skills of negotiation and
              compromise.{" "}
            </p>
          </div>
          <div class="electors-template-img">
            <img src="./convention.png" alt="An Electors' Convention" />
          </div>
        </section>
        <section
          className="electors-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="electors-template-text">
            <h1>No Majority</h1>
            <h2>Without a majority party, electors must deliberate.</h2>
            <p>
              When the electors assemble at the seat of government, the Hamilton
              Method will have prevented any party from achieving a majority
              electoral vote. Electors will be forced into deliberation. One of
              the electors must be able to build a coalition of 270 electors to
              be chosen as the next president.
            </p>
          </div>
          <div class="electors-template-img">
            <img
              src="./capitolwithelectors.png"
              alt="An Electors' Convention"
            />
          </div>
        </section>
        <section className="electors-template">
          <div className="electors-template-text">
            <h1>Key Points of an Electors' Convention:</h1>
            <p>• Creates a deliberative environment</p>
            <p>• Compromise and negotiation are required</p>
            <p>• Populism and tyranny are ineffective</p>
            <p>• Money is not required to hold an electors' convention</p>
            <p>• Foreign intelligence services cannot influence</p>
            <p>• Mass media and social media cannot influence</p>
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default ElectorsConvention;
