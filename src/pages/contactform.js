import React, { useState } from 'react';
import './contactform.css';

const ContactForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    message: '',
  });

  const [responseMessage, setResponseMessage] = useState('');
  const [responseStatus, setResponseStatus] = useState('');

  const handleChange = (e) => {
    const { name, value } = e.target;
    setFormData({ ...formData, [name]: value });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();

    try {
      const response = await fetch('http://framersmethod.com/contactform.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams(formData).toString(),
      });

      const result = await response.json();
      setResponseStatus(result.status);
      setResponseMessage(result.message);

      if (result.status === 'success') {
        setFormData({
          name: '',
          email: '',
          message: '',
        });
      }

    } catch (error) {
      console.error('Error submitting the form:', error);
      setResponseStatus('error');
      setResponseMessage('An error occurred while sending your message.');
    }
  };

  return (
    <div className="contact">
      <div className="contactform-send-message">
        Send a message to the Framers' Method:
      </div>
      <form onSubmit={handleSubmit}>
        <ul>
          <li>
            <label htmlFor="name">Name:</label>
            <input
              type="text"
              id="name"
              name="name"
              value={formData.name}
              onChange={handleChange}
              required
            />
          </li>
          <li>
            <label htmlFor="email">Email:</label>
            <input
              type="email"
              id="email"
              name="email"
              value={formData.email}
              onChange={handleChange}
              required
            />
          </li>
          <li>
            <label htmlFor="message">Message:</label>
            <textarea
              id="message"
              name="message"
              value={formData.message}
              onChange={handleChange}
              required
            ></textarea>
          </li>
          <li className='button'>
            <button type="submit">Send</button>
          </li>
        </ul>
      </form>
      {responseMessage && (
        <p className={responseStatus === 'success' ? 'success' : 'error'}>
          {responseMessage}
        </p>
      )}
    </div>
  );
};

export default ContactForm;