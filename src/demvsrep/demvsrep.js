import React from "react";
import { SocialMediaBar } from "../socialmediabar";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./demvsrep.css";

const DemVsRep = () => {
  return (
    <PageTransition>
      <section>
        <Helmet>
          {/* Essential/Basic Meta Tags */}
          <title>
            The Framers' Method: Electoral College & Hamilton Method Explained -
            Democracy vs Republic
          </title>
          <meta
            name="description"
            content="What is the difference between a democracy and a republic? Find out at the Framers' Method."
          />
          <meta
            name="keywords"
            content="electoral college, presidential elections, american politics, electors, republic, president, constitution, democracy, electors convention"
          />
          <meta name="author" content="Dustin Taylor" />
          <meta name="robots" content="index, follow"></meta>
          <meta
            http-equiv="Content-Type"
            content="text/html; charset=utf-8"
          ></meta>
          <meta name="language" content="English"></meta>
          {/* Open Graph Tags */}
          <meta property="og:type" content="website" />
          <meta
            property="og:url"
            content="https://www.framersmethod.com/demvsrep"
          />
          <meta property="og:site_name" content="The Framers' Method" />
          <meta
            property="og:title"
            content="The Framers' Method - A Democracy vs a Republic"
          />
          <meta
            property="og:description"
            content="What is the difference between a democracy and a republic? Find out at the Framers' Method."
          />
          <meta
            property="og:image"
            content="https://www.framersmethod.com/framersmethod-withbackground.png"
          />
          <meta property="og:image:width" content="1200" />
          <meta property="og:image:height" content="630" />
          {/* Twitter Tags */}
          <meta name="twitter:site" content="@framersmethod" />
          <meta
            name="twitter:title"
            content="The Framers' Method - A Democracy vs a Republic"
          />
          <meta
            name="twitter:description"
            content="What is the difference between a democracy and a republic? Find out at the Framers' Method."
          />
          <meta
            name="twitter:image"
            content="https://www.framersmethod.com/framersmethod-withbackground.png"
          />
          <meta name="twitter:card" content="summary_large_image" />
        </Helmet>
        <div className="demvsrep-logo">
          <img src="../demvsreplogo2.png" alt="Democracy versus Republic" />
        </div>
        <SocialMediaBar />
        <section className="demvsrep-template">
          <div className="demvsrep-template-text">
            <h1>Democracy</h1>
            <p>
              Derived from Greek, with dēmos meaning “the people” and -kratiā
              meaning “rule.” Also referred to as direct democracy or pure
              democracy.
            </p>
          </div>
          <div class="demvsrep-template-img">
            <img src="../greekdemocracy.png" alt="Greek Democracy" />
          </div>
        </section>

        <section
          className="demvsrep-template"
          style={{ backgroundColor: "#f5f4f0" }}
        >
          <div className="demvsrep-template-text">
            <h1>Republic</h1>
            <p>
              Derived from Latin, with rēs meaning “thing” and pūblicus meaning
              “of the people” or "of the public." The Republic is a system where
              the people create a small body or several small bodies to make the
              rules for society. Republics may also be referred to as
              representative democracy or constitutional government.
            </p>
          </div>
          <div class="demvsrep-template-img">
            <img src="../romanrepublic.png" alt="Roman republic" />
          </div>
        </section>

        <section className="demvsrep-template">
          <div className="demvsrep-template-text">
            <h1>Democracy vs. Republic</h1>
            <p>
              A democracy is a form of government in which the people directly
              determine the rules. In contrast, a republic is a system where the
              people elect a smaller group of representatives to create the
              rules on their behalf.
            </p>
            <p>
              In regards to the Electoral College, the small group of
              representatives would be electors and these electors would choose
              the president on behalf of the people.
            </p>
          </div>
          <div class="demvsrep-template-img">
            <img src="../demvsrep2.png" alt="Democracy versus Republic" />
          </div>
        </section>
      </section>
    </PageTransition>
  );
};

export default DemVsRep;
