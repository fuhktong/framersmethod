import React from "react";
import { Helmet } from "react-helmet-async";
import PageTransition from "../pagetransition.js";
import "./book.css";

export const DividerWhite = () => (
  <section>
    <div className="dividerwhite"></div>
  </section>
);

const Book = () => (
  <PageTransition>
    <section className="book" style={{ backgroundColor: "white" }}>
      <Helmet>
        {/* Essential/Basic Meta Tags */}
        <title>
          The Framers' Method: Electoral College & Hamilton Method Explained -
          Book
        </title>
        <meta charset="utf-8" />
        <meta
          name="description"
          content="Buy the book! The American republic is under threat from populism and tyrannical-minded politicians. The Framers’ Method can restore political stability to government and the American people."
        />
        <meta
          name="keywords"
          content="electoral college, presidential elections, american politics, electors, electoral votes, president, constitution, amazon, electors convention"
        />
        <meta name="author" content="Dustin Taylor" />
        <meta name="language" content="English" />
        <meta name="robots" content="index, follow" />
        <meta http-equiv="Content-Type" content="text/html"></meta>

        {/* Open Graph Tags */}
        <meta property="og:type" content="website" />
        <meta property="og:url" content="https://www.framersmethod.com/book" />
        <meta property="og:site_name" content="The Framers' Method" />
        <meta property="og:title" content="The Framers' Method - Book" />
        <meta
          property="og:description"
          content="Buy the book! The American republic is under threat from populism and tyrannical-minded politicians. The Framers’ Method can restore political stability to government and the American people."
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
        <meta name="twitter:title" content="The Framers' Method - Book" />
        <meta
          name="twitter:description"
          content="Buy the book! The American republic is under threat from populism and tyrannical-minded politicians. The Framers’ Method can restore political stability to government and the American people."
        />
        <meta
          name="twitter:image"
          content="https://www.framersmethod.com/framersmethodlogo-withbackground.png"
        />
      </Helmet>
      <div className="book-description">
        <div className="book-text">
          <h2>On the Framers' Electoral College</h2>
        </div>
        <div className="book-text">
          <h1>
            How the Hamilton Method and an Electors' Convention Can Defeat
            Populism and Tyranny{" "}
          </h1>
        </div>
        <div className="book-text">
          <p>
            Whatever you think about the Electoral College, it is not of the
            framers’ design. Diving deep into the realm of political science,
            the reader will discover what the framers actually created, how
            truly different the framers’ Electoral College is from our modern
            incarnation, and reforms that will bring us back to the framers’
            original concept of choosing a president.
            <br />
            Using original analysis and data, The Framers’ Method describes how
            the delegates to the constitutional convention built a system of
            decentralization and deliberation to elect a president. By
            dispersing power to electors throughout the several states to
            nominate candidates, “designing men” would be unable to influence
            and control the masses. By returning to the framers’ method, we can
            save the United States from the destructiveness of populism and
            tyranny.
          </p>
        </div>
        <div className="book-text">
          {" "}
          <a
            className="amazonlink"
            href="https://a.co/d/2kLold3"
            target="_blank"
            rel="noreferrer"
          >
            Purchase here on Amazon
          </a>
        </div>
      </div>
      <div class="book-img">
        <img
          src="./bookimage.png"
          alt="On the Framers' Method: How the Electoral College and the Hamilton Method Can Defeat Populism and Tyranny"
        />
      </div>
    </section>
  </PageTransition>
);

export default Book;
