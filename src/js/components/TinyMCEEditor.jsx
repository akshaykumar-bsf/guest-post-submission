import React, { useEffect, useRef, useState, forwardRef, useImperativeHandle } from 'react';

const TinyMCEEditor = forwardRef(({ content, onChange, error, errorId, required = false, ariaInvalid = "false" }, ref) => {
  const editorRef = useRef(null);
  const textareaRef = useRef(null);
  const editorId = 'gps-tinymce-editor';
  const [localContent, setLocalContent] = useState(content || '');
  const [isFocused, setIsFocused] = useState(false);
  
  // Expose methods to parent component
  useImperativeHandle(ref, () => ({
    resetContent: () => {
      setLocalContent('');
      if (window.tinymce && window.tinymce.get(editorId)) {
        window.tinymce.get(editorId).setContent('');
      }
      if (textareaRef.current) {
        textareaRef.current.value = '';
      }
    }
  }));
  
  // Keep local content in sync with parent component
  useEffect(() => {
    setLocalContent(content || '');
    
    // Update TinyMCE content if it exists and content is different
    if (window.tinymce && window.tinymce.get(editorId)) {
      const editor = window.tinymce.get(editorId);
      if (editor && editor.getContent() !== content) {
        editor.setContent(content || '');
      }
    }
  }, [content]);
  
  useEffect(() => {
    // Simple fallback if TinyMCE is not available
    if (!window.tinymce) {
      console.log('TinyMCE not available, using simple textarea');
      return;
    }
    
    // Check if editor is already initialized
    if (window.tinymce.get(editorId)) {
      return;
    }
    
    // Use a simple textarea with basic formatting
    const initEditor = () => {
      try {
        window.tinymce.init({
          selector: `#${editorId}`,
          height: 300,
          menubar: false,
          plugins: [
            'lists', 'link', 'paste'
          ],
          toolbar: 'undo redo | bold italic | bullist numlist | link',
          content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; }',
          setup: (editor) => {
            editorRef.current = editor;
            
            // Set initial content
            editor.on('init', () => {
              editor.setContent(localContent);
              
              // Add ARIA attributes to the editor iframe
              const editorIframe = document.querySelector(`#${editorId}_ifr`);
              if (editorIframe) {
                editorIframe.setAttribute('title', 'Post content editor');
                editorIframe.setAttribute('aria-label', 'Post content editor');
                if (required) {
                  editorIframe.setAttribute('aria-required', 'true');
                }
              }
              
              // Add ARIA attributes to the editor container
              const editorContainer = document.querySelector(`.tox-tinymce[aria-label="${editorId}"]`);
              if (editorContainer) {
                editorContainer.setAttribute('aria-label', 'Post content editor');
                if (required) {
                  editorContainer.setAttribute('aria-required', 'true');
                }
                editorContainer.setAttribute('aria-invalid', ariaInvalid);
                if (errorId) {
                  editorContainer.setAttribute('aria-describedby', errorId);
                }
              }
            });
            
            // Handle content changes
            editor.on('change input blur', () => {
              const newContent = editor.getContent();
              setLocalContent(newContent);
              onChange(newContent);
            });
            
            // Handle focus state for accessibility
            editor.on('focus', () => {
              setIsFocused(true);
            });
            
            editor.on('blur', () => {
              setIsFocused(false);
            });
          },
          // Accessibility settings
          browser_spellcheck: true,
          contextmenu: false,
          statusbar: true
        });
      } catch (e) {
        console.error('Error initializing TinyMCE:', e);
      }
    };
    
    // Initialize editor with a small delay to ensure DOM is ready
    const timerId = setTimeout(initEditor, 100);
    
    return () => {
      // Clean up
      clearTimeout(timerId);
      
      // Remove TinyMCE instance when component unmounts
      if (window.tinymce && window.tinymce.get(editorId)) {
        try {
          window.tinymce.remove(`#${editorId}`);
        } catch (e) {
          console.log('Error removing TinyMCE:', e);
        }
      }
    };
  }, [required, ariaInvalid, errorId]);
  
  // Handle changes directly from textarea if TinyMCE fails to load
  const handleTextareaChange = (e) => {
    const newContent = e.target.value;
    setLocalContent(newContent);
    
    if (!window.tinymce || !window.tinymce.get(editorId)) {
      onChange(newContent);
    }
  };
  
  // Handle focus and blur for the fallback textarea
  const handleFocus = () => setIsFocused(true);
  const handleBlur = () => setIsFocused(false);
  
  return (
    <div className="mb-4">
      <label className="block text-sm font-medium mb-1" htmlFor={editorId}>
        Post Content <span className="text-red-500" aria-hidden="true">*</span>
        <span className="sr-only">(required)</span>
      </label>
      <div className={`editor-wrapper ${isFocused ? 'focused' : ''} ${error ? 'has-error' : ''}`}>
        <textarea
          id={editorId}
          ref={textareaRef}
          value={localContent}
          onChange={handleTextareaChange}
          onFocus={handleFocus}
          onBlur={handleBlur}
          className={`w-full border ${error ? 'border-red-500' : 'border-gray-300'} rounded p-2 min-h-[200px] text-sm`}
          aria-required={required ? "true" : "false"}
          aria-invalid={ariaInvalid}
          aria-describedby={errorId}
        />
      </div>
      {error && (
        <p 
          className="text-red-500 text-xs mt-1" 
          id={errorId || "post_content_error"}
          role="alert"
        >
          {error}
        </p>
      )}
      <p className="text-gray-500 text-xs mt-1" id="editor_description">
        Use the toolbar to format your content. You can add links, lists, and basic formatting.
      </p>
    </div>
  );
});

export default TinyMCEEditor;
