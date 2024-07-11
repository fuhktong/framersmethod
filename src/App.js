import './global.css';
import { Routes, Route } from 'react-router-dom';
import { HelmetProvider } from 'react-helmet-async';
import Header from './Header.js';
import Footer from './Footer.js';
import Home from './pages/home.js'
import News from './pages/news.js';
import Archive from './pages/archive.js'
import RepVsDem from './pages/repvsdem.js';
import Hamilton from './pages/hamilton.js';
import Electors from './pages/electors.js';
import Book from './pages/book.js';
import FAQ from './pages/faq.js';
import Contribute from './pages/contribute.js';
import Contact from './pages/contact.js';
import Video001 from './newssource/video001.js'
import Video002 from './newssource/video002.js'
import Video003 from './newssource/video003.js'
import Video004 from './newssource/video004.js'
import Video005 from './newssource/video005.js'
import Video006 from './newssource/video006.js'
import Video007 from './newssource/video007.js'
import News001 from './newssource/joe-biden-stepping-down.js';

const helmetContext = {};

const App = () => {
  return (
    <HelmetProvider context={helmetContext}>
    <div>
      <Header />
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="news" element={<News />} />
          <Route path="archive" element={<Archive />} />
          <Route path="repvsdem" element={<RepVsDem />} />
          <Route path="hamilton" element={<Hamilton />} />
          <Route path="electors" element={<Electors />} />
          <Route path="book" element={<Book />} />
          <Route path="faq" element={<FAQ />} />
          <Route path="contribute" element={<Contribute />} />
          <Route path="contact" element={<Contact />} />
          <Route path="video001" element={<Video001 />} />
          <Route path="video002" element={<Video002 />} />
          <Route path="video003" element={<Video003 />} />
          <Route path="video004" element={<Video004 />} />
          <Route path="video005" element={<Video005 />} />
          <Route path="video006" element={<Video006 />} />
          <Route path="video007" element={<Video007 />} />
          <Route path="joe-biden-stepping-down" element={<News001 />} />
        </Routes>
      <Footer />
    </div>
    </HelmetProvider>
  );
};

export default App;