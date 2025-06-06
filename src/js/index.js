import React from 'react';
import ReactDOM from 'react-dom';
import SubmissionForm from './components/SubmissionForm';
import '../css/tailwind.css';
import '../css/custom.css';

document.addEventListener('DOMContentLoaded', function() {
  const formContainer = document.getElementById('gps-submission-form');
  
  if (formContainer) {
    ReactDOM.render(
      <SubmissionForm />,
      formContainer
    );
  }
});
