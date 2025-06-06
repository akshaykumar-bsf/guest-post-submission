<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #f5f5f5; padding: 15px; border-bottom: 2px solid #ddd; }
        .content { padding: 20px 0; }
        .footer { border-top: 1px solid #ddd; padding-top: 15px; font-size: 12px; color: #777; }
        .button { display: inline-block; padding: 10px 15px; background-color: #0073aa; color: white; text-decoration: none; border-radius: 3px; margin-right: 10px; }
        .button.reject { background-color: #d63638; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>New Guest Post Submission</h2>
        </div>
        <div class="content">
            <p>A new guest post has been submitted and is awaiting your review.</p>
            
            <h3>Post Details:</h3>
            <p><strong>Title:</strong> {post_title}</p>
            <p><strong>Author:</strong> {author_name} ({author_email})</p>
            <p><strong>Submitted on:</strong> {submission_date}</p>
            
            <p>
                <a href="{preview_link}" class="button">Preview Post</a>
                <a href="{approve_link}" class="button">Approve</a>
                <a href="{reject_link}" class="button reject">Reject</a>
            </p>
        </div>
        <div class="footer">
            <p>This email was sent from your WordPress site.</p>
        </div>
    </div>
</body>
</html>
