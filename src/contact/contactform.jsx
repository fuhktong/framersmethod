import React, { useState } from 'react';
import './contactform.css'; 

const ContactForm = () => {
  const [formData, setFormData] = useState({
    name: '',
    email: '',
    message: ''
  });
  const [status, setStatus] = useState('');
  const [isSubmitting, setIsSubmitting] = useState(false);

  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setStatus('Sending...');
    
    try {
      const response = await fetch('https://framersmethod.com/process_form.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData)
      });
      
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      
      const responseText = await response.text();
      
      let data;
      try {
        data = JSON.parse(responseText);
      } catch (parseError) {
        console.error('Raw response:', responseText);
        throw new Error('Invalid JSON response from server');
      }
      
      if (data.success) {
        setStatus('Message sent successfully!');
        setFormData({ name: '', email: '', message: '' });
      } else {
        setStatus(data.message || 'Failed to send message');
      }
    } catch (error) {
      console.error('Error:', error);
      setStatus(error.message || 'An error occurred. Please try again.');
    } finally {
      setIsSubmitting(false);
    }
  };

  const handleChange = (e) => {
    setFormData({
      ...formData,
      [e.target.name]: e.target.value
    });
  };

  return (
    <div className="contact">
      <div className="contactform-send-message">Send us a message</div>
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
            />
          </li>
          
          <li className="button">
            <button type="submit" disabled={isSubmitting}>
              {isSubmitting ? 'Sending...' : 'Send'}
            </button>
          </li>
        </ul>
        
        {status && (
          <div className={status.includes('success') ? 'success' : 'error'}>
            {status}
          </div>
        )}
      </form>
    </div>
  );
};

export default ContactForm;