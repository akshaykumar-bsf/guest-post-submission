import React, { useState, useCallback, useRef, useEffect } from 'react';
import TinyMCEEditor from './TinyMCEEditor';

const SubmissionForm = () => {
  const [formData, setFormData] = useState({
    post_title: '',
    post_content: '',
    author_name: '',
    author_email: '',
    author_bio: '',
    featured_image: null
  });
  
  const [errors, setErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [submitResult, setSubmitResult] = useState(null);
  const fileInputRef = useRef(null);
  const editorRef = useRef(null);
  const formRef = useRef(null);
  const successMessageRef = useRef(null);
  const formFieldRefs = {
    post_title: useRef(null),
    post_content: useRef(null),
    author_name: useRef(null),
    author_email: useRef(null),
    author_bio: useRef(null),
    featured_image: useRef(null)
  };
  
  // Announce submission result to screen readers
  useEffect(() => {
    if (submitResult) {
      if (submitResult.type === 'success' && formRef.current) {
        // Scroll to form top when submission is successful
        formRef.current.scrollIntoView({ behavior: 'smooth', block: 'start' });
        
        // Focus on success message for screen readers
        if (successMessageRef.current) {
          setTimeout(() => {
            successMessageRef.current.focus();
          }, 100);
        }
      }
    }
  }, [submitResult]);
  
  // Scroll to first error field when errors change
  useEffect(() => {
    const errorFields = Object.keys(errors);
    if (errorFields.length > 0) {
      const firstErrorField = errorFields[0];
      const errorRef = formFieldRefs[firstErrorField];
      
      if (errorRef && errorRef.current) {
        errorRef.current.scrollIntoView({ behavior: 'smooth', block: 'center' });
        errorRef.current.focus();
        
        // Announce error to screen readers
        const errorMessage = `Error: ${errors[firstErrorField]}`;
        announceToScreenReader(errorMessage);
      }
    }
  }, [errors]);
  
  // Function to announce messages to screen readers
  const announceToScreenReader = (message) => {
    const announcement = document.createElement('div');
    announcement.setAttribute('aria-live', 'assertive');
    announcement.setAttribute('role', 'alert');
    announcement.classList.add('sr-only'); // Screen reader only
    announcement.textContent = message;
    document.body.appendChild(announcement);
    
    // Remove after announcement is read
    setTimeout(() => {
      document.body.removeChild(announcement);
    }, 1000);
  };
  
  // Use useCallback to prevent unnecessary re-renders
  const handleChange = useCallback((e) => {
    const { name, value } = e.target;
    setFormData(prevData => ({
      ...prevData,
      [name]: value
    }));
  }, []);
  
  // Use useCallback for content changes from TinyMCE
  const handleContentChange = useCallback((content) => {
    setFormData(prevData => ({
      ...prevData,
      post_content: content
    }));
  }, []);
  
  const handleImageChange = useCallback((e) => {
    setFormData(prevData => ({
      ...prevData,
      featured_image: e.target.files[0]
    }));
    
    // Announce file selection to screen readers
    if (e.target.files && e.target.files[0]) {
      announceToScreenReader(`File selected: ${e.target.files[0].name}`);
    }
  }, []);
  
  const resetForm = () => {
    // Reset React state
    setFormData({
      post_title: '',
      post_content: '',
      author_name: '',
      author_email: '',
      author_bio: '',
      featured_image: null
    });
    
    // Reset file input
    if (fileInputRef.current) {
      fileInputRef.current.value = '';
    }
    
    // Reset TinyMCE editor
    if (window.tinymce) {
      const editor = window.tinymce.get('gps-tinymce-editor');
      if (editor) {
        editor.setContent('');
      }
    }
    
    // If we have a reference to the editor component, tell it to reset
    if (editorRef.current && typeof editorRef.current.resetContent === 'function') {
      editorRef.current.resetContent();
    }
  };
  
  const handleSubmit = async (e) => {
    e.preventDefault();
    setIsSubmitting(true);
    setErrors({});
    
    // Announce form submission to screen readers
    announceToScreenReader('Submitting form, please wait...');
    
    // Form validation
    let newErrors = {};
    if (!formData.post_title.trim()) {
      newErrors.post_title = 'Title is required';
    }
    
    if (!formData.post_content.trim()) {
      newErrors.post_content = 'Content is required';
    }
    
    if (!formData.author_name.trim()) {
      newErrors.author_name = 'Name is required';
    }
    
    if (!formData.author_email.trim()) {
      newErrors.author_email = 'Email is required';
    } else if (!/\S+@\S+\.\S+/.test(formData.author_email)) {
      newErrors.author_email = 'Email is invalid';
    }
    
    if (Object.keys(newErrors).length > 0) {
      setErrors(newErrors);
      setIsSubmitting(false);
      return;
    }
    
    // Submit form data
    const data = new FormData();
    for (const key in formData) {
      if (key === 'featured_image' && formData[key]) {
        data.append(key, formData[key]);
      } else if (formData[key]) {
        data.append(key, formData[key]);
      }
    }
    
    // Note: We don't include category - it will be set to "Submissions" on the backend
    data.append('action', 'gps_submit_post');
    data.append('nonce', window.gpsData ? window.gpsData.nonce : '');
    
    try {
      const response = await fetch(window.gpsData ? window.gpsData.ajax_url : '/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: data,
        credentials: 'same-origin'
      });
      
      const result = await response.json();
      
      if (result.success) {
        setSubmitResult({
          type: 'success',
          message: result.data.message
        });
        
        // Reset the form completely
        resetForm();
        
        // Announce success to screen readers
        announceToScreenReader('Form submitted successfully. The form has been reset for a new submission.');
      } else {
        setSubmitResult({
          type: 'error',
          message: result.data.message
        });
        if (result.data.errors) {
          setErrors(result.data.errors);
        }
        
        // Announce error to screen readers
        announceToScreenReader('There was an error submitting the form. Please check the form for errors.');
      }
    } catch (error) {
      setSubmitResult({
        type: 'error',
        message: 'An error occurred while submitting the form.'
      });
      
      // Announce error to screen readers
      announceToScreenReader('A technical error occurred while submitting the form. Please try again later.');
    }
    
    setIsSubmitting(false);
  };
  
  return (
    <div className="gps-form-container max-w-3xl mx-auto p-4">
      <h1 className="text-2xl font-bold mb-6 text-center">Submit a Guest Post</h1>
      
      {/* Screen reader only instructions */}
      <div className="sr-only" aria-live="polite">
        This is a form to submit a guest post. All fields marked with an asterisk are required.
      </div>
      
      {submitResult && (
        <div 
          className={`p-4 mb-6 rounded ${submitResult.type === 'success' ? 'bg-green-100 text-green-800 success-message' : 'bg-red-100 text-red-800 error-message'}`}
          role="alert"
          ref={submitResult.type === 'success' ? successMessageRef : null}
          tabIndex={-1}
          aria-atomic="true"
        >
          {submitResult.message}
        </div>
      )}
      
      <form ref={formRef} onSubmit={handleSubmit} className="space-y-6" noValidate aria-label="Guest post submission form">
        <div className="form-field">
          <label htmlFor="post_title" className="text-sm font-medium">
            Post Title <span className="text-red-500" aria-hidden="true">*</span>
            <span className="sr-only">(required)</span>
          </label>
          <input
            type="text"
            id="post_title"
            name="post_title"
            value={formData.post_title}
            onChange={handleChange}
            required
            aria-required="true"
            aria-invalid={errors.post_title ? "true" : "false"}
            aria-describedby={errors.post_title ? "post_title_error" : undefined}
            className={`w-full border ${errors.post_title ? 'border-red-500' : 'border-gray-300'} rounded p-2 text-sm`}
            ref={formFieldRefs.post_title}
          />
          {errors.post_title && (
            <p 
              className="text-red-500 text-xs mt-1" 
              id="post_title_error"
              role="alert"
            >
              {errors.post_title}
            </p>
          )}
        </div>
        
        <div ref={formFieldRefs.post_content}>
          <TinyMCEEditor
            ref={editorRef}
            content={formData.post_content}
            onChange={handleContentChange}
            error={errors.post_content}
            errorId={errors.post_content ? "post_content_error" : undefined}
            required={true}
            ariaInvalid={errors.post_content ? "true" : "false"}
          />
        </div>
        
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div className="form-field">
            <label htmlFor="author_name" className="text-sm font-medium">
              Your Name <span className="text-red-500" aria-hidden="true">*</span>
              <span className="sr-only">(required)</span>
            </label>
            <input
              type="text"
              id="author_name"
              name="author_name"
              value={formData.author_name}
              onChange={handleChange}
              required
              aria-required="true"
              aria-invalid={errors.author_name ? "true" : "false"}
              aria-describedby={errors.author_name ? "author_name_error" : undefined}
              className={`w-full border ${errors.author_name ? 'border-red-500' : 'border-gray-300'} rounded p-2 text-sm`}
              ref={formFieldRefs.author_name}
            />
            {errors.author_name && (
              <p 
                className="text-red-500 text-xs mt-1" 
                id="author_name_error"
                role="alert"
              >
                {errors.author_name}
              </p>
            )}
          </div>
          
          <div className="form-field">
            <label htmlFor="author_email" className="text-sm font-medium">
              Your Email <span className="text-red-500" aria-hidden="true">*</span>
              <span className="sr-only">(required)</span>
            </label>
            <input
              type="email"
              id="author_email"
              name="author_email"
              value={formData.author_email}
              onChange={handleChange}
              required
              aria-required="true"
              aria-invalid={errors.author_email ? "true" : "false"}
              aria-describedby={errors.author_email ? "author_email_error" : undefined}
              className={`w-full border ${errors.author_email ? 'border-red-500' : 'border-gray-300'} rounded p-2 text-sm`}
              ref={formFieldRefs.author_email}
            />
            {errors.author_email && (
              <p 
                className="text-red-500 text-xs mt-1" 
                id="author_email_error"
                role="alert"
              >
                {errors.author_email}
              </p>
            )}
          </div>
        </div>
        
        <div className="form-field">
          <label htmlFor="author_bio" className="text-sm font-medium">
            About You <span className="text-red-500" aria-hidden="true">*</span>
            <span className="sr-only">(required)</span>
          </label>
          <textarea
            id="author_bio"
            name="author_bio"
            value={formData.author_bio}
            onChange={handleChange}
            rows={4}
            required
            aria-required="true"
            aria-invalid={errors.author_bio ? "true" : "false"}
            aria-describedby={errors.author_bio ? "author_bio_error" : undefined}
            className={`w-full border ${errors.author_bio ? 'border-red-500' : 'border-gray-300'} rounded p-2 text-sm`}
            ref={formFieldRefs.author_bio}
          ></textarea>
          {errors.author_bio && (
            <p 
              className="text-red-500 text-xs mt-1" 
              id="author_bio_error"
              role="alert"
            >
              {errors.author_bio}
            </p>
          )}
        </div>
        
        <div className="form-field">
          <label htmlFor="featured_image" className="text-sm font-medium">Featured Image</label>
          <input
            type="file"
            id="featured_image"
            name="featured_image"
            onChange={handleImageChange}
            className={`w-full border ${errors.featured_image ? 'border-red-500' : 'border-gray-300'} rounded p-2 text-sm`}
            accept="image/*"
            aria-invalid={errors.featured_image ? "true" : "false"}
            aria-describedby="featured_image_description"
            ref={(el) => {
              fileInputRef.current = el;
              formFieldRefs.featured_image.current = el;
            }}
          />
          {errors.featured_image && (
            <p 
              className="text-red-500 text-xs mt-1" 
              id="featured_image_error"
              role="alert"
            >
              {errors.featured_image}
            </p>
          )}
          <p className="text-gray-500 text-xs mt-1" id="featured_image_description">
            Maximum file size: 2MB. Recommended dimensions: 1200x628 pixels.
          </p>
        </div>
        
        <div className="text-center">
          <button
            type="submit"
            disabled={isSubmitting}
            className="bg-primary hover:bg-primary-dark text-white font-bold py-2 px-8 rounded focus:outline-none focus:shadow-outline transition duration-300 text-sm"
            aria-busy={isSubmitting ? "true" : "false"}
          >
            {isSubmitting ? 'Submitting...' : 'Submit Post'}
          </button>
        </div>
      </form>
      
      {/* Hidden element for screen reader announcements */}
      <div 
        className="sr-only" 
        aria-live="assertive" 
        role="status"
      >
        {isSubmitting ? 'Submitting form, please wait...' : ''}
      </div>
    </div>
  );
};

export default SubmissionForm;
