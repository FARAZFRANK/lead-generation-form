/**
 * Lead Generation Form - Form Generator Script
 */

(function($) {
	'use strict';

	const isPro = (typeof FormGenerator !== 'undefined' && FormGenerator.IsPro) ? true : false;

	function decodeHtmlEntities(str) {
		if (!str || typeof str !== 'string') {
			return '';
		}
		const txt = document.createElement('textarea');
		txt.innerHTML = str;
		return txt.value;
	}

	function safeEscape(str) {
		if (str === null || str === undefined) {
			return '';
		}
		const decoded = decodeHtmlEntities(String(str));
		return $('<div>').text(decoded).html();
	}

	const formTemplates = {
		contact: {
			name: 'Classic Contact Form',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Get in Touch with Us' },
				{ type: 'paragraph', subtype: 'p', label: 'Please fill out the form below and our team will get back to you within 24 hours.' },
				{ type: 'text', label: 'Your Full Name', name: 'Name', required: true, placeholder: 'e.g. John Doe' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'john@example.com' },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'Phone', required: false, placeholder: '+1 (555) 000-0000' },
				{ type: 'text', label: 'Subject', name: 'Subject', required: false, placeholder: 'Inquiry regarding services' },
				{ type: 'textarea', label: 'Your Message', name: 'Message', required: true, placeholder: 'How can we help you today?' },
				{ type: 'button', subtype: 'submit', label: 'Send Message', style: 'primary' }
			]
		},
		quote: {
			name: 'Request a Quote & Cost Estimator',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Step 1: Contact Details' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Jane Smith' },
				{ type: 'text', subtype: 'email', label: 'Business Email', name: 'Email', required: true, placeholder: 'jane@company.com' },
				{ type: 'step-break', label: 'Project Scope' },
				{ type: 'select', label: 'Service Needed', name: 'Service', values: [
					{ label: 'Web Development ($2,500)', value: '2500', selected: true },
					{ label: 'Mobile App Development ($4,500)', value: '4500' },
					{ label: 'UI/UX Design & Branding ($1,500)', value: '1500' },
					{ label: 'SEO & Growth Marketing ($1,000)', value: '1000' }
				]},
				{ type: 'select', label: 'Project Timeline', name: 'Timeline', values: [
					{ label: 'Standard (4-6 weeks)', value: '0', selected: true },
					{ label: 'Rush / Express (+ $500)', value: '500' }
				]},
				{ type: 'calculation', label: 'Estimated Total Cost', name: 'Estimated_Cost', formula: '{Service} + {Timeline}', prefix: '$', decimals: '0' },
				{ type: 'step-break', label: 'Timeline & Notes' },
				{ type: 'date', label: 'Target Launch Date', name: 'Launch_Date' },
				{ type: 'textarea', label: 'Project Scope & Details', name: 'Project_Details', placeholder: 'Describe your deliverables, goals, and timeline...' },
				{ type: 'button', subtype: 'submit', label: 'Request Official Estimate', style: 'primary' }
			]
		},
		demo: {
			name: 'B2B Demo Booking & Discovery',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Schedule a 1-on-1 Product Demo' },
				{ type: 'paragraph', subtype: 'p', label: 'See how our platform streamlines operations. Choose a time that works for your team.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Alex Johnson' },
				{ type: 'text', subtype: 'email', label: 'Work Email', name: 'Email', required: true, placeholder: 'alex@yourcompany.com' },
				{ type: 'text', label: 'Company / Organization', name: 'Company', required: true, placeholder: 'Acme Inc.' },
				{ type: 'select', label: 'Team Size', name: 'Team_Size', values: [
					{ label: '1 - 10 Employees', value: '1_10' },
					{ label: '11 - 50 Employees', value: '11_50', selected: true },
					{ label: '51 - 200 Employees', value: '51_200' },
					{ label: '200+ Enterprise', value: '200_plus' }
				]},
				{ type: 'select', label: 'Primary Objective', name: 'Objective', values: [
					{ label: 'Increase Lead Conversion', value: 'conversion', selected: true },
					{ label: 'Automate Email Marketing', value: 'automation' },
					{ label: 'Consolidate CRM Data', value: 'crm' },
					{ label: 'Other Custom Needs', value: 'other' }
				]},
				{ type: 'date', label: 'Preferred Demo Date', name: 'Demo_Date', required: true },
				{ type: 'button', subtype: 'submit', label: 'Book Demo Session', style: 'primary' }
			]
		},
		realestate: {
			name: 'Real Estate Property Tour & Inquiry',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Schedule a Private Property Tour' },
				{ type: 'paragraph', subtype: 'p', label: 'Connect with our licensed property advisors for exclusive walkthroughs and pricing info.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Sarah Connor' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'sarah@example.com' },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'Phone', required: true, placeholder: '+1 (555) 234-5678' },
				{ type: 'select', label: 'Property of Interest', name: 'Property_Type', values: [
					{ label: 'Downtown Luxury Condo ($650k - $1.2M)', value: 'condo', selected: true },
					{ label: 'Suburban Single Family Home ($450k - $800k)', value: 'suburban' },
					{ label: 'Commercial Retail / Office Space', value: 'commercial' },
					{ label: 'Vacation Rental / Investment Villa', value: 'villa' }
				]},
				{ type: 'radio-group', label: 'Financing Status', name: 'Financing', values: [
					{ label: 'Pre-Approved for Mortgage', value: 'pre_approved', selected: true },
					{ label: 'Cash Buyer', value: 'cash' },
					{ label: 'Need Mortgage Assistance', value: 'need_help' }
				]},
				{ type: 'date', label: 'Preferred Tour Date', name: 'Tour_Date' },
				{ type: 'textarea', label: 'Specific Neighborhoods or Questions', name: 'Notes', placeholder: 'Tell us about your must-haves, preferred bedrooms, or timing...' },
				{ type: 'button', subtype: 'submit', label: 'Schedule Tour', style: 'primary' }
			]
		},
		agency: {
			name: 'Agency Client Intake & Project Scope',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Step 1: Brand & Contact Info' },
				{ type: 'text', label: 'Your Name & Title', name: 'Name', required: true, placeholder: 'David Miller, CMO' },
				{ type: 'text', subtype: 'email', label: 'Business Email', name: 'Email', required: true, placeholder: 'david@brand.com' },
				{ type: 'text', label: 'Current Website URL', name: 'Website', placeholder: 'https://yourbrand.com' },
				{ type: 'step-break', label: 'Services & Goals' },
				{ type: 'checkbox-group', label: 'Select Required Deliverables', name: 'Services', values: [
					{ label: 'Custom WordPress / Web Development', value: 'web', selected: true },
					{ label: 'Brand Identity & Design System', value: 'branding' },
					{ label: 'Paid Media (Google / Meta Ads)', value: 'ads' },
					{ label: 'Technical SEO & Content Strategy', value: 'seo' }
				]},
				{ type: 'select', label: 'Target Launch Timeline', name: 'Timeline', values: [
					{ label: 'Immediate (Within 30 Days)', value: '30_days' },
					{ label: '1 - 3 Months', value: '1_3_months', selected: true },
					{ label: 'Flexible / Exploring Options', value: 'flexible' }
				]},
				{ type: 'step-break', label: 'Budget & Details' },
				{ type: 'select', label: 'Allocated Project Budget', name: 'Budget', values: [
					{ label: '$5,000 - $10,000', value: '5k_10k' },
					{ label: '$10,000 - $25,000', value: '10k_25k', selected: true },
					{ label: '$25,000 - $50,000', value: '25k_50k' },
					{ label: '$50,000+', value: '50k_plus' }
				]},
				{ type: 'textarea', label: 'Key Objectives & Target Audience', name: 'Scope_Notes', placeholder: 'What are your primary KPIs and success metrics for this engagement?' },
				{ type: 'button', subtype: 'submit', label: 'Submit Project Scope', style: 'primary' }
			]
		},
		consultation: {
			name: 'Free Consultation Scheduling',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Book Your Free 30-Minute Consultation' },
				{ type: 'paragraph', subtype: 'p', label: 'Get actionable insights tailored to your business goals. Zero commitment required.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Michael Brown' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'michael@startup.io' },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'Phone', required: true, placeholder: '+1 (555) 345-6789' },
				{ type: 'select', label: 'Consultation Topic', name: 'Topic', values: [
					{ label: 'Digital Transformation & Cloud', value: 'digital', selected: true },
					{ label: 'Lead Generation & Sales Funnels', value: 'lead_gen' },
					{ label: 'Custom Plugin / Web Development', value: 'dev' },
					{ label: 'Performance Audit & SEO', value: 'seo' }
				]},
				{ type: 'date', label: 'Preferred Consultation Date', name: 'Date', required: true },
				{ type: 'radio-group', label: 'Preferred Time Window', name: 'Time_Slot', values: [
					{ label: 'Morning (9:00 AM - 12:00 PM)', value: 'morning', selected: true },
					{ label: 'Afternoon (1:00 PM - 5:00 PM)', value: 'afternoon' }
				]},
				{ type: 'textarea', label: 'Briefly describe what you would like to achieve', name: 'Goals', placeholder: 'Current bottlenecks, tools, and goals...' },
				{ type: 'button', subtype: 'submit', label: 'Confirm Free Booking', style: 'primary' }
			]
		},
		ebook: {
			name: 'Gated E-Book & Whitepaper Download',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Download the 2026 Growth Playbook' },
				{ type: 'paragraph', subtype: 'p', label: 'Get immediate access to our 45-page comprehensive industry research paper.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Emily Watson' },
				{ type: 'text', subtype: 'email', label: 'Business Email', name: 'Email', required: true, placeholder: 'emily@enterprise.com' },
				{ type: 'text', label: 'Job Title', name: 'Title', required: true, placeholder: 'Head of Growth' },
				{ type: 'text', label: 'Company Name', name: 'Company', required: true, placeholder: 'ScaleUp Corp' },
				{ type: 'select', label: 'Industry Vertical', name: 'Industry', values: [
					{ label: 'SaaS & Technology', value: 'saas', selected: true },
					{ label: 'E-Commerce & Retail', value: 'ecommerce' },
					{ label: 'Healthcare & Biotech', value: 'healthcare' },
					{ label: 'Finance & Real Estate', value: 'finance' }
				]},
				{ type: 'button', subtype: 'submit', label: 'Download Free PDF', style: 'primary' }
			]
		},
		waitlist: {
			name: 'SaaS Early Access & Beta Waitlist',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Join the VIP Early Access Waitlist' },
				{ type: 'paragraph', subtype: 'p', label: 'Be the first to access our upcoming 2.0 release. Limited beta invitations rolling out weekly.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Lucas Vance' },
				{ type: 'text', subtype: 'email', label: 'Your Best Email', name: 'Email', required: true, placeholder: 'lucas@producthunt.com' },
				{ type: 'select', label: 'Your Primary Role', name: 'Role', values: [
					{ label: 'Founder / CEO', value: 'founder', selected: true },
					{ label: 'Product Manager', value: 'pm' },
					{ label: 'Full-Stack Developer', value: 'dev' },
					{ label: 'Marketing Specialist', value: 'marketing' }
				]},
				{ type: 'radio-group', label: 'Estimated Team Seats Needed', name: 'Seats', values: [
					{ label: 'Solo (1 User)', value: '1' },
					{ label: 'Small Team (2-5 Users)', value: '2_5', selected: true },
					{ label: 'Mid Size (6-20 Users)', value: '6_20' },
					{ label: 'Enterprise (20+ Users)', value: '20_plus' }
				]},
				{ type: 'textarea', label: 'What feature are you most excited to use?', name: 'Feature_Wishlist', placeholder: 'Tell us your workflow pain points...' },
				{ type: 'button', subtype: 'submit', label: 'Claim Your VIP Spot', style: 'primary' }
			]
		},
		csat_nps: {
			name: 'CSAT & Net Promoter Score (NPS)',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Customer Experience & NPS Survey' },
				{ type: 'paragraph', subtype: 'p', label: 'Your honest feedback directly shapes our product roadmap.' },
				{ type: 'nps-matrix', label: 'How likely are you to recommend us to a colleague or friend? (0 = Not at all, 10 = Extremely Likely)', name: 'NPS_Score', required: true },
				{ type: 'star-rating', label: 'Overall Service Quality Rating (1 to 5 Stars)', name: 'Quality_Rating', required: true },
				{ type: 'radio-group', label: 'How easy was it to complete your purchase/inquiry?', name: 'Effort_Score', required: true, values: [
					{ label: 'Very Easy', value: 'very_easy' },
					{ label: 'Moderate', value: 'moderate' },
					{ label: 'Difficult', value: 'difficult' }
				]},
				{ type: 'textarea', label: 'What is the primary reason for your score?', name: 'Feedback_Reason', placeholder: 'Tell us what we did well or how we can improve...' },
				{ type: 'text', label: 'Your Name (Optional)', name: 'Name', placeholder: 'Optional' },
				{ type: 'text', subtype: 'email', label: 'Email (Optional, for follow-up)', name: 'Email', placeholder: 'Optional' },
				{ type: 'button', subtype: 'submit', label: 'Submit NPS Feedback', style: 'primary' }
			]
		},
		ux_bug: {
			name: 'Website UX Feedback & Bug Report',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Report a Bug or Usability Issue' },
				{ type: 'text', label: 'Your Name', name: 'Name', required: true },
				{ type: 'text', subtype: 'email', label: 'Your Email', name: 'Email', required: true },
				{ type: 'text', label: 'Page URL Where Issue Occurred', name: 'URL', placeholder: 'https://example.com/checkout' },
				{ type: 'select', label: 'Issue Severity', name: 'Severity', values: [
					{ label: 'Low - Visual / Cosmetic Glitch', value: 'low' },
					{ label: 'Medium - Feature Partially Broken', value: 'medium', selected: true },
					{ label: 'High - Cannot Complete Action / Checkout', value: 'high' }
				]},
				{ type: 'textarea', label: 'Steps to Reproduce & Expected Result', name: 'Steps', required: true, placeholder: '1. Clicked on button X\n2. Expected Y but saw Z...' },
				{ type: 'file', label: 'Upload Screenshot or Screen Recording', name: 'Screenshot' },
				{ type: 'button', subtype: 'submit', label: 'Send Bug Report', style: 'primary' }
			]
		},
		employee_pulse: {
			name: 'Employee Pulse & Team Feedback',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Monthly Employee Pulse Check' },
				{ type: 'paragraph', subtype: 'p', label: 'Anonymous feedback to help leadership support and empower our team.' },
				{ type: 'select', label: 'Your Department', name: 'Department', required: true, placeholder: 'Select your department', values: [
					{ label: 'Engineering & Product', value: 'eng' },
					{ label: 'Marketing & Growth', value: 'marketing' },
					{ label: 'Sales & Customer Success', value: 'sales' },
					{ label: 'Operations & HR', value: 'ops' }
				]},
				{ type: 'star-rating', label: 'Work-Life Balance Satisfaction', name: 'Balance_Rating', required: true },
				{ type: 'star-rating', label: 'Team Collaboration & Support', name: 'Teamwork_Rating', required: true },
				{ type: 'radio-group', label: 'Do you feel you have the resources needed to succeed?', name: 'Resources_Check', required: true, values: [
					{ label: 'Yes, fully equipped', value: 'yes' },
					{ label: 'Somewhat, need some tooling', value: 'somewhat' },
					{ label: 'No, missing key resources', value: 'no' }
				]},
				{ type: 'textarea', label: 'What is one thing we could change to make your workday better?', name: 'Suggestions', required: true, placeholder: 'Any suggestions or concerns...' },
				{ type: 'button', subtype: 'submit', label: 'Submit Confidential Feedback', style: 'primary' }
			]
		},
		product_review: {
			name: 'Product Review & Testimonial Collector',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Share Your Product Experience' },
				{ type: 'text', label: 'Your Full Name', name: 'Name', required: true, placeholder: 'Jordan Lee' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true },
				{ type: 'text', label: 'Product or Plan Purchased', name: 'Product', required: true, placeholder: 'Pro Plan' },
				{ type: 'star-rating', label: 'Overall Product Rating', name: 'Rating' },
				{ type: 'text', label: 'Review Headline', name: 'Headline', required: true, placeholder: 'e.g. Best lead generation tool we have used!' },
				{ type: 'textarea', label: 'Detailed Review & Results Achieved', name: 'Review', required: true, placeholder: 'How did this product help your workflow or business revenue?' },
				{ type: 'checkbox-group', label: 'Permission to Feature', name: 'Permission', values: [
					{ label: 'I agree to have my review displayed on the website / marketing materials', value: 'agree', selected: true }
				]},
				{ type: 'button', subtype: 'submit', label: 'Publish Review', style: 'primary' }
			]
		},
		conference: {
			name: 'Conference & Webinar Registration',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Step 1: Attendee Profile' },
				{ type: 'text', label: 'Attendee Full Name', name: 'Name', required: true, placeholder: 'Dr. Robert Smith' },
				{ type: 'text', subtype: 'email', label: 'Work Email Address', name: 'Email', required: true, placeholder: 'robert@university.edu' },
				{ type: 'text', label: 'Company / Organization', name: 'Company', required: true, placeholder: 'BioTech Global' },
				{ type: 'step-break', label: 'Session Tracks' },
				{ type: 'header', subtype: 'h3', label: 'Step 2: Session Tracks & Pass Type' },
				{ type: 'select', label: 'Registration Pass Type', name: 'Pass_Type', values: [
					{ label: 'Virtual Attendee (Free Live Stream)', value: 'virtual', selected: true },
					{ label: 'All-Access In-Person Pass ($299)', value: 'in_person' },
					{ label: 'VIP Executive & Networking Pass ($599)', value: 'vip' }
				]},
				{ type: 'checkbox-group', label: 'Select Workshop Tracks', name: 'Workshops', values: [
					{ label: 'Keynote: AI & Automated Lead Generation', value: 'keynote', selected: true },
					{ label: 'Hands-on: Conversion Rate Optimization', value: 'cro', selected: true },
					{ label: 'Panel: Scaling Enterprise Sales Funnels', value: 'scaling' }
				]},
				{ type: 'step-break', label: 'Accommodations' },
				{ type: 'header', subtype: 'h3', label: 'Step 3: Accommodations & Notes' },
				{ type: 'textarea', label: 'Dietary Restrictions or Special Accessibility Needs', name: 'Accessibility' },
				{ type: 'button', subtype: 'submit', label: 'Complete Conference Registration', style: 'primary' }
			]
		},
		workshop: {
			name: 'Workshop & Masterclass Enrollment',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Reserve Your Seat in the Masterclass' },
				{ type: 'text', label: 'Student Full Name', name: 'Name', required: true, placeholder: 'Amanda Clark' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'amanda@design.io' },
				{ type: 'text', subtype: 'tel', label: 'Contact Phone', name: 'Phone', required: true, placeholder: '+1 (555) 456-7890' },
				{ type: 'select', label: 'Experience Level', name: 'Skill_Level', values: [
					{ label: 'Beginner (0-1 Years Experience)', value: 'beginner' },
					{ label: 'Intermediate (2-4 Years Experience)', value: 'intermediate', selected: true },
					{ label: 'Advanced Practitioner (5+ Years)', value: 'advanced' }
				]},
				{ type: 'radio-group', label: 'Preferred Workshop Cohort', name: 'Cohort', values: [
					{ label: 'Saturday Weekend Intensive (10:00 AM - 4:00 PM)', value: 'weekend', selected: true },
					{ label: 'Tuesday/Thursday Evening Sessions (6:00 PM - 8:30 PM)', value: 'evening' }
				]},
				{ type: 'textarea', label: 'What specific skill do you hope to master?', name: 'Goals', placeholder: 'Tell the instructor what you want to learn...' },
				{ type: 'button', subtype: 'submit', label: 'Enroll in Masterclass', style: 'primary' }
			]
		},
		restaurant_vip: {
			name: 'Restaurant Table & VIP Reservation',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Table & VIP Dining Reservation' },
				{ type: 'text', label: 'Guest Name for Reservation', name: 'Name', required: true, placeholder: 'Marcus Sterling' },
				{ type: 'text', subtype: 'email', label: 'Confirmation Email', name: 'Email', required: true, placeholder: 'marcus@luxury.com' },
				{ type: 'text', subtype: 'tel', label: 'Contact Phone', name: 'Phone', required: true, placeholder: '+1 (555) 789-0123' },
				{ type: 'number', label: 'Number of Guests in Party', name: 'Guests', value: '2', min: '1', max: '20', required: true },
				{ type: 'date', label: 'Reservation Date', name: 'Date', required: true },
				{ type: 'select', label: 'Preferred Seating Area', name: 'Seating_Area', values: [
					{ label: 'Main Dining Hall', value: 'main', selected: true },
					{ label: 'Outdoor Garden Patio', value: 'patio' },
					{ label: 'Private VIP Wine Room (+$50 deposit)', value: 'vip' }
				]},
				{ type: 'textarea', label: 'Special Occasion, Allergies or Requests', name: 'Special_Requests', placeholder: 'Birthday celebration, anniversary, gluten-free accommodations...' },
				{ type: 'button', subtype: 'submit', label: 'Confirm Reservation', style: 'primary' }
			]
		},
		volunteer: {
			name: 'Community Volunteer Signup',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Join Our Community Volunteer Program' },
				{ type: 'text', label: 'Volunteer Full Name', name: 'Name', required: true, placeholder: 'Hannah Davis' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'hannah@community.org' },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'Phone', required: true, placeholder: '+1 (555) 890-1234' },
				{ type: 'checkbox-group', label: 'Areas of Interest & Skills', name: 'Interests', values: [
					{ label: 'Event Setup & Logistics', value: 'event_setup', selected: true },
					{ label: 'Youth Mentoring & Tutoring', value: 'tutoring' },
					{ label: 'Social Media & Photography', value: 'media' },
					{ label: 'Food Drive & Distribution', value: 'food_drive', selected: true }
				]},
				{ type: 'radio-group', label: 'General Availability', name: 'Availability', values: [
					{ label: 'Weekends (Saturday/Sunday)', value: 'weekends', selected: true },
					{ label: 'Weekday Evenings', value: 'evenings' },
					{ label: 'Flexible On-Call', value: 'flexible' }
				]},
				{ type: 'textarea', label: 'Emergency Contact Info & Additional Skills', name: 'Emergency_Info', required: true, placeholder: 'Contact Name, Phone, and any certifications (e.g. CPR)...' },
				{ type: 'button', subtype: 'submit', label: 'Sign Up as Volunteer', style: 'primary' }
			]
		},
		job_application: {
			name: 'Job Application & Resume Uploader',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Career Application' },
				{ type: 'paragraph', subtype: 'p', label: 'Join our growing team. Please submit your details, portfolio, and resume below.' },
				{ type: 'text', label: 'Full Name', name: 'Name', required: true, placeholder: 'Christian Taylor' },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'Email', required: true, placeholder: 'christian@applicant.com' },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'Phone', required: true, placeholder: '+1 (555) 901-2345' },
				{ type: 'select', label: 'Position Applied For', name: 'Position', values: [
					{ label: 'Senior Frontend Engineer (React/Vue/JS)', value: 'frontend', selected: true },
					{ label: 'Senior Backend Developer (PHP/WordPress/Node)', value: 'backend' },
					{ label: 'Product Designer (Figma / UI/UX)', value: 'design' },
					{ label: 'Technical Customer Success Manager', value: 'cs' }
				]},
				{ type: 'text', label: 'LinkedIn or Portfolio URL', name: 'Portfolio', placeholder: 'https://linkedin.com/in/username' },
				{ type: 'file', label: 'Upload Your Resume / CV (PDF or DOCX)', name: 'Resume', required: true, allowed_types: 'pdf, doc, docx', max_file_size: 10 },
				{ type: 'textarea', label: 'Cover Letter / Why are you a great fit?', name: 'Cover_Letter', placeholder: 'Brief summary of relevant achievements and why you want to work with us...' },
				{ type: 'button', subtype: 'submit', label: 'Submit Application', style: 'primary' }
			]
		},
		support: {
			name: 'IT Helpdesk & Priority Support Ticket',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Submit a Priority Support Ticket' },
				{ type: 'text', label: 'Contact Name', name: 'Name', required: true, placeholder: 'Olivia Martinez' },
				{ type: 'text', subtype: 'email', label: 'Contact Email', name: 'Email', required: true, placeholder: 'olivia@company.com' },
				{ type: 'select', label: 'Issue Priority', name: 'Priority', values: [
					{ label: 'Low - General Inquiry / Guidance', value: 'low' },
					{ label: 'Medium - Normal Issue / Minor Bug', value: 'medium', selected: true },
					{ label: 'High - Core Feature Impaired', value: 'high' },
					{ label: 'Critical - System Outage / Blocking Sales', value: 'critical' }
				]},
				{ type: 'text', label: 'Issue Summary', name: 'Summary', required: true, placeholder: 'Brief one-line overview of the problem' },
				{ type: 'textarea', label: 'Detailed Description & Steps to Reproduce', name: 'Description', required: true, placeholder: 'Describe what happened and how to replicate it...' },
				{ type: 'file', label: 'Attach Error Log or Screenshot', name: 'Attachment', allowed_types: 'png, jpg, jpeg, log, txt, pdf', max_file_size: 10 },
				{ type: 'button', subtype: 'submit', label: 'Open Support Ticket', style: 'primary' }
			]
		},
		digital_order: {
			name: 'Digital Product Order & Instant Checkout',
			payment_settings: {
				payment_enabled: 1,
				payment_type: 'calculation',
				payment_calc_field: 'Total_Price',
				payment_item_name: 'Digital Software Order'
			},
			data: [
				{ type: 'header', subtype: 'h3', label: 'Complete Your Digital Software Order' },
				{ type: 'text', label: 'Customer Full Name', name: 'Name', required: true, placeholder: 'Brandon Scott' },
				{ type: 'text', subtype: 'email', label: 'License Delivery Email', name: 'Email', required: true, placeholder: 'brandon@digital.com' },
				{ type: 'select', label: 'Select License Tier', name: 'Package', values: [
					{ label: 'Starter License (1 Site) - $49', value: '49', selected: true },
					{ label: 'Agency License (5 Sites) - $149', value: '149' },
					{ label: 'Unlimited Lifetime Enterprise - $299', value: '299' }
				]},
				{ type: 'select', label: 'Optional Add-on', name: 'Addon', values: [
					{ label: 'No Add-on', value: '0', selected: true },
					{ label: 'Priority 1-Hour SLA Support (+ $29)', value: '29' },
					{ label: 'Full Installation & Setup Service (+ $99)', value: '99' }
				]},
				{ type: 'calculation', label: 'Total Amount to Pay', name: 'Total_Price', formula: '{Package} + {Addon}', prefix: '$', decimals: '2' },
				{ type: 'radio-group', label: 'Preferred Payment Method', name: 'Payment_Method', values: [
					{ label: 'Credit / Debit Card (Stripe)', value: 'stripe', selected: true },
					{ label: 'PayPal Express', value: 'paypal' }
				]},
				{ type: 'button', subtype: 'submit', label: 'Proceed to Secure Checkout', style: 'primary' }
			]
		},
		retainer: {
			name: 'Monthly Service Retainer Agreement',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Select Your Dedicated Monthly Retainer' },
				{ type: 'text', label: 'Client / Business Name', name: 'Name', required: true, placeholder: 'Apex Dynamics LLC' },
				{ type: 'text', subtype: 'email', label: 'Billing Email Address', name: 'Email', required: true, placeholder: 'billing@apexdynamics.com' },
				{ type: 'select', label: 'Retainer Service Tier', name: 'Tier_Price', values: [
					{ label: 'Bronze - 10 Dedicated Hours / Mo ($850/mo)', value: '850', selected: true },
					{ label: 'Silver - 25 Dedicated Hours / Mo ($1,950/mo)', value: '1950' },
					{ label: 'Gold - 50 Dedicated Hours / Mo ($3,500/mo)', value: '3500' }
				]},
				{ type: 'calculation', label: 'Monthly Investment Total', name: 'Monthly_Total', formula: '{Tier_Price}', prefix: '$', decimals: '0' },
				{ type: 'date', label: 'Desired Retainer Start Date', name: 'Start_Date', required: true },
				{ type: 'textarea', label: 'Initial Scope / Month 1 Priority Tasks', name: 'Month1_Tasks', placeholder: 'List the top initiatives for your first sprint...' },
				{ type: 'button', subtype: 'submit', label: 'Confirm Retainer Scope', style: 'primary' }
			]
		},
		donation: {
			name: 'Nonprofit Donation & Contribution',
			payment_settings: {
				payment_enabled: 1,
				payment_type: 'calculation',
				payment_calc_field: 'Total_Donation',
				payment_item_name: 'Nonprofit Contribution'
			},
			data: [
				{ type: 'header', subtype: 'h3', label: 'Support Our Community Cause' },
				{ type: 'paragraph', subtype: 'p', label: '100% of your tax-deductible donation directly funds grassroots programs and aid.' },
				{ type: 'text', label: 'Donor Full Name', name: 'Name', required: true, placeholder: 'Eleanor Vance' },
				{ type: 'text', subtype: 'email', label: 'Tax Receipt Email', name: 'Email', required: true, placeholder: 'eleanor@philanthropy.org' },
				{ type: 'select', label: 'Donation Amount', name: 'Donation_Amount', values: [
					{ label: '$25 - Provides 10 Community Meals', value: '25' },
					{ label: '$50 - Supplies Education Books', value: '50', selected: true },
					{ label: '$100 - Family Emergency Support Kit', value: '100' },
					{ label: '$250 - Sponsor a Youth Workshop', value: '250' }
				]},
				{ type: 'calculation', label: 'Total Contribution', name: 'Total_Donation', formula: '{Donation_Amount}', prefix: '$', decimals: '0' },
				{ type: 'textarea', label: 'Dedication Note / In Honor of (Optional)', name: 'Dedication', placeholder: 'Optional dedication or message to the community...' },
				{ type: 'button', subtype: 'submit', label: 'Complete Donation', style: 'primary' }
			]
		},
		real_estate: {
			name: 'Real Estate Property Valuation & Lead Form',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Step 1: Property Details' },
				{ type: 'select', label: 'Property Type', name: 'property_type', values: [
					{ label: 'Single Family House', value: 'single_family', selected: true },
					{ label: 'Condo / Apartment', value: 'condo' },
					{ label: 'Townhouse', value: 'townhouse' },
					{ label: 'Commercial Property', value: 'commercial' }
				]},
				{ type: 'text', label: 'Property Address', name: 'property_address', required: true },
				{ type: 'number', label: 'Approx. Square Footage', name: 'square_footage', placeholder: 'e.g. 1800' },
				{ type: 'step-break', label: 'Contact Info' },
				{ type: 'header', subtype: 'h3', label: 'Step 2: Where Should We Send the Valuation?' },
				{ type: 'text', label: 'Full Name', name: 'full_name', required: true },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'email', required: true },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'phone', required: true },
				{ type: 'button', subtype: 'submit', label: 'Get Free Valuation', style: 'primary' }
			]
		},
		gym_fitness: {
			name: 'Gym & Fitness Membership Application',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Fitness Club Membership Form' },
				{ type: 'text', label: 'Member Name', name: 'member_name', required: true },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'email', required: true },
				{ type: 'text', subtype: 'tel', label: 'Phone Number', name: 'phone', required: true },
				{ type: 'radio-group', label: 'Select Membership Tier', name: 'membership_plan', values: [
					{ label: 'Standard Gym Access ($29/mo)', value: '29', selected: true },
					{ label: 'Pro + Classes & Sauna ($59/mo)', value: '59' },
					{ label: 'VIP Unlimited + Personal Training ($99/mo)', value: '99' }
				]},
				{ type: 'paragraph', label: 'I hereby declare that I am physically sound to participate in exercise routines. I agree to club policies.' },
				{ type: 'signature', label: 'Member Digital Signature', name: 'signature', required: true },
				{ type: 'button', subtype: 'submit', label: 'Submit Application', style: 'primary' }
			]
		},
		dental_medical: {
			name: 'Dental & Medical Appointment Booking',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Book Dental Consultation' },
				{ type: 'text', label: 'Patient Full Name', name: 'patient_name', required: true },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'email', required: true },
				{ type: 'text', subtype: 'tel', label: 'Mobile Phone', name: 'phone', required: true },
				{ type: 'select', label: 'Treatment / Concern', name: 'treatment', values: [
					{ label: 'Routine Cleaning & Checkup', value: 'cleaning', selected: true },
					{ label: 'Teeth Whitening', value: 'whitening' },
					{ label: 'Invisalign / Braces Consultation', value: 'orthodontics' },
					{ label: 'Toothache / Urgent Dental Pain', value: 'emergency' }
				]},
				{ type: 'date', label: 'Preferred Appointment Date', name: 'preferred_date', required: true },
				{ type: 'textarea', label: 'Notes or Specific Symptoms', name: 'patient_notes' },
				{ type: 'button', subtype: 'submit', label: 'Confirm Appointment Request', style: 'primary' }
			]
		},
		cost_estimator: {
			name: 'Website & Project Cost Estimator',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Instant Project Estimator' },
				{ type: 'text', label: 'Your Name', name: 'client_name', required: true },
				{ type: 'text', subtype: 'email', label: 'Business Email', name: 'email', required: true },
				{ type: 'number', label: 'Number of Pages Needed ($100/page)', name: 'num_pages', value: '5', min: '1', max: '50' },
				{ type: 'checkbox-group', label: 'Add-on Features', name: 'addons', values: [
					{ label: 'E-Commerce Store Setup ($350)', value: '350' },
					{ label: 'Advanced SEO Package ($200)', value: '200' },
					{ label: 'Multi-Language Support ($150)', value: '150' }
				]},
				{ type: 'calculation', label: 'Estimated Project Total', name: 'estimated_total', formula: '({num_pages} * 100) + {addons}', prefix: '$', decimals: '0' },
				{ type: 'textarea', label: 'Project Description / Scope', name: 'project_brief' },
				{ type: 'button', subtype: 'submit', label: 'Request Proposal', style: 'primary' }
			]
		},
		auto_repair: {
			name: 'Automotive Repair & Service Quote',
			data: [
				{ type: 'header', subtype: 'h3', label: 'Vehicle Service & Inspection Request' },
				{ type: 'text', label: 'Your Full Name', name: 'customer_name', required: true },
				{ type: 'text', subtype: 'tel', label: 'Mobile Phone Number', name: 'phone', required: true },
				{ type: 'text', subtype: 'email', label: 'Email Address', name: 'email', required: true },
				{ type: 'text', label: 'Vehicle Year, Make & Model', name: 'vehicle_info', placeholder: 'e.g. 2021 Honda Civic', required: true },
				{ type: 'select', label: 'Primary Service Needed', name: 'service_type', values: [
					{ label: 'Brake Pad & Rotor Inspection', value: 'brakes', selected: true },
					{ label: 'Oil Change & Comprehensive Service', value: 'oil_change' },
					{ label: 'Check Engine Light Diagnostics', value: 'diagnostics' },
					{ label: 'Transmission / Engine Work', value: 'major_mechanical' },
					{ label: 'AC & Climate Control Repair', value: 'ac_repair' }
				]},
				{ type: 'file', label: 'Upload Photo of Issue or Dashboard (Optional)', name: 'vehicle_photo' },
				{ type: 'textarea', label: 'Describe Symptoms or Strange Noises', name: 'issue_description' },
				{ type: 'button', subtype: 'submit', label: 'Request Instant Quote', style: 'primary' }
			]
		}
	};
	formTemplates.realestate = formTemplates.real_estate;

	window.WLGF_FormGenerator = {
		copy: function(wlgf_form_id, triggerBtn) {
			const shortcodeText = '[WLFG id="' + wlgf_form_id + '"]';

			if (navigator.clipboard && window.isSecureContext) {
				navigator.clipboard.writeText(shortcodeText);
			} else {
				const tempInput = document.createElement('input');
				tempInput.value = shortcodeText;
				document.body.appendChild(tempInput);
				tempInput.select();
				document.execCommand('copy');
				document.body.removeChild(tempInput);
			}

			// Visual feedback on button
			const $btn = triggerBtn ? $(triggerBtn) : $('#wlgf-shortcode-btn-' + wlgf_form_id);
			if ($btn.length) {
				const origHtml = $btn.html();
				$btn.addClass('wlgf-shortcode-embed-box__btn--success').html('<i class="fa-solid fa-check"></i> <span>Copied!</span>');
				setTimeout(function() {
					$btn.removeClass('wlgf-shortcode-embed-box__btn--success').html(origHtml);
				}, 2000);
			}

			const $tooltip = $('#wlgf-copied-' + wlgf_form_id);
			if ($tooltip.length) {
				$tooltip.addClass('wlgf-code-pill__copied-tooltip--active');
				setTimeout(function() {
					$tooltip.removeClass('wlgf-code-pill__copied-tooltip--active');
				}, 2000);
			}
			return false;
		}
	};

	window.WLGF = function(wlgf_form_id, do_action, triggerBtn) {
		if (do_action === 'copy') {
			return window.WLGF_FormGenerator.copy(wlgf_form_id, triggerBtn);
		}
		return false;
	};

	$(document).ready(function() {
		const typeUserEventsConfig = {};

		// Cleanly sanitize FormBuilder confirmation dialogs to center properly, remove scrollbars and prevent bottom sticking
		const dialogObserver = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				mutation.addedNodes.forEach(function(node) {
					if (node.nodeType === 1 && node.classList && node.classList.contains('form-builder-dialog')) {
						node.style.position = 'fixed';
						node.style.top = '50%';
						node.style.left = '50%';
						node.style.transform = 'translate(-50%, -50%)';
						node.style.margin = '0';
						node.style.overflow = 'hidden';
						node.style.overflowY = 'hidden';
						node.style.maxHeight = 'none';
						node.style.height = 'auto';
					}
				});
			});
		});
		dialogObserver.observe(document.body, { childList: true });

		// Intercept paste on FormBuilder editable labels to prevent pasting formatted HTML (e.g. <span style="font-family:">)
		$(document).on('paste', '#build-wrap [contenteditable], #build-wrap .field-label, #build-wrap .fld-label, #build-wrap input.option-label', function(e) {
			const clipboardEvent = e.originalEvent || e;
			if (clipboardEvent.clipboardData && clipboardEvent.clipboardData.getData) {
				const text = clipboardEvent.clipboardData.getData('text/plain');
				if (text !== undefined) {
					e.preventDefault();
					if (this.isContentEditable) {
						window.document.execCommand('insertText', false, text);
					} else {
						const start = this.selectionStart;
						const end = this.selectionEnd;
						const val = $(this).val();
						$(this).val(val.substring(0, start) + text + val.substring(end)).trigger('change');
						this.selectionStart = this.selectionEnd = start + text.length;
					}
				}
			}
		});

		const fbEditor = document.getElementById('build-wrap');
		let formBuilder = null;

		const colWidthFieldAttr = {
			column_width: {
				label: 'Field Width (Column Layout)',
				type: 'select',
				options: isPro ? {
					'12': '100% (Full Width)',
					'6': '50% (Half Width - 2 Columns)',
					'4': '33.3% (One-Third - 3 Columns)',
					'3': '25% (One-Fourth - 4 Columns)'
				} : {
					'12': '100% (Full Width)',
					'pro_6': '50% (2 Columns) ★ PRO',
					'pro_4': '33.3% (3 Columns) ★ PRO',
					'pro_3': '25% (4 Columns) ★ PRO'
				},
				value: '12'
			}
		};

		function syncCanvasButtonStyles() {
			if (typeof syncThemeStylerToCanvas === 'function') {
				syncThemeStylerToCanvas();
			}
		}

		const fbOptions = {
			i18n: {
				locale: 'en-US',
				location: (typeof FormGenerator !== 'undefined' && FormGenerator.pluginUrl ? FormGenerator.pluginUrl : '') + 'admin/assets/lang/'
			},
			scrollToFieldOnAdd: false,
			stickyControls: {
				enable: false
			},
			onAddField: function(fieldId) {
				if (!isPro) {
					const proFieldTypes = ['calculation', 'step-break', 'star-rating', 'nps-matrix', 'signature'];
					setTimeout(function() {
						const fieldEl = document.getElementById(fieldId);
						if (fieldEl) {
							const $field = $(fieldEl);
							const fType = $field.attr('type') || $field.data('type') || '';
							if (proFieldTypes.indexOf(fType) !== -1) {
								$field.remove();
								const rawLabel = fType.replace(/-/g, ' ').replace(/\b\w/g, function(l) { return l.toUpperCase(); });
								$('#wlgf-pro-modal-title').text('Unlock ' + rawLabel + ' (PRO Feature)');
								$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
								if (typeof wlgfShowToast === 'function') {
									wlgfShowToast(rawLabel + ' is a PRO feature. Upgrade to unlock.', 'warning');
								}
							}
						}
					}, 20);
				}
				if (typeof syncCanvasColumnClasses === 'function') {
					setTimeout(syncCanvasColumnClasses, 60);
				}
				if (typeof syncCanvasButtonStyles === 'function') {
					setTimeout(function() {
						$('.frmb li.form-field.button-field').each(function() {
							const $clsInput = $(this).find('.className-wrap input, .fld-className');
							if (!$clsInput.val() || $clsInput.val() === 'btn-default btn') {
								$clsInput.val('btn-primary btn').trigger('change');
							}
						});
						$('.frmb li.form-field.button-field .tooltip-element, .frmb li.form-field.header-field .tooltip-element, .frmb li.form-field.paragraph-field .tooltip-element, .frmb li.form-field.divider-field .tooltip-element, .frmb li.form-field.step-break-field .tooltip-element').remove();
						if (typeof syncCanvasButtonStyles === 'function') {
							syncCanvasButtonStyles();
						}
					}, 60);
				}
				if (typeof initCanvasFlatpickrs === 'function') {
					setTimeout(initCanvasFlatpickrs, 80);
				}
				if (typeof syncFieldOptionsUi === 'function') {
					setTimeout(syncFieldOptionsUi, 80);
				}
			},
			disabledActionButtons: ['data', 'save'],
			disableFields: ['autocomplete'],
			controlOrder: ['header', 'paragraph', 'divider', 'text', 'textarea', 'select', 'radio-group', 'checkbox-group', 'date', 'number', 'file', 'star-rating', 'nps-matrix', 'calculation', 'terms', 'hidden', 'step-break', 'signature', 'button'],
			disabledAttrs: ['access', 'other', 'toggle', 'inline'],
			typeUserDisabledAttrs: {
				button: ['description', 'access', 'other', 'toggle', 'inline', 'required'],
				header: ['description', 'access', 'other', 'toggle', 'inline', 'required'],
				paragraph: ['description', 'access', 'other', 'toggle', 'inline', 'required'],
				divider: ['description', 'access', 'other', 'toggle', 'inline', 'required'],
				'step-break': ['description', 'access', 'other', 'toggle', 'inline', 'required']
			},
			disabledSubtypes: {
				paragraph: ['address', 'blockquote', 'canvas', 'output'],
				button: ['button', 'reset'],
				textarea: ['tinymce', 'quill']
			},
			fields: [
				{
					label: 'Phone Number',
					type: 'text',
					subtype: 'tel',
					icon: '<i class="fa-solid fa-phone"></i>',
					className: 'form-control'
				},
				{
					label: 'Website URL',
					type: 'text',
					subtype: 'url',
					icon: '<i class="fa-solid fa-link"></i>',
					className: 'form-control'
				},
				{
					label: 'Section Divider',
					type: 'divider',
					icon: '<i class="fa-solid fa-minus"></i>',
					className: 'wlgf-form-divider'
				},
				{
					label: 'GDPR / Terms Checkbox',
					type: 'terms',
					icon: '<i class="fa-solid fa-shield-halved"></i>',
					className: 'wlgf-terms-agreement'
				},
				{
					label: 'Step Break (Page Break)',
					type: 'step-break',
					icon: '<i class="fa-solid fa-layer-group"></i>',
					className: 'wlgf-step-break'
				},
				{
					label: 'Star Rating (1-5)',
					type: 'star-rating',
					icon: '<i class="fa-solid fa-star"></i>',
					className: 'wlgf-star-rating-field'
				},
				{
					label: 'NPS Score Matrix (0-10)',
					type: 'nps-matrix',
					icon: '<i class="fa-solid fa-gauge-high"></i>',
					className: 'wlgf-nps-matrix-field'
				},
				{
					label: 'Calculation / Price Estimator',
					type: 'calculation',
					icon: '<i class="fa-solid fa-calculator"></i>',
					className: 'wlgf-calculation-field'
				},
				{
					label: 'Digital Signature Pad',
					type: 'signature',
					icon: '<i class="fa-solid fa-pen-nib"></i>',
					className: 'wlgf-signature-field'
				},
				{
					label: 'Submit',
					type: 'button',
					subtype: 'submit',
					icon: '<i class="fa-solid fa-square-check"></i>',
					className: 'btn-primary btn',
					style: 'primary'
				}
			],
			templates: {
				'divider': function(fieldData) {
					const divLabel = safeEscape(fieldData.label && fieldData.label !== 'Section Divider' ? fieldData.label : '── Section Divider ──');
					return {
						field: '<div class="wlgf-preview-divider"><hr class="wlgf-form-divider"><span class="wlgf-text-muted small">' + divLabel + '</span></div>',
						onRender: function() {}
					};
				},
				'terms': function(fieldData) {
					const termsLabel = safeEscape(fieldData.label || 'I agree to the Terms of Service and Privacy Policy');
					return {
						field: '<div class="wlgf-preview-terms"><input type="checkbox" checked disabled> <span>' + termsLabel + ' <span class="text-danger">*</span></span></div>',
						onRender: function() {}
					};
				},
				'step-break': function(fieldData) {
					const stepLabel = safeEscape(fieldData.label || 'Step Break (Page Break)');
					return {
						field: '<div class="wlgf-step-break"><i class="fa-solid fa-layer-group"></i> <strong>── ' + stepLabel + ' ──</strong></div>',
						onRender: function() {}
					};
				},
				'star-rating': function(fieldData) {
					return {
						field: '<div class="wlgf-preview-stars"><i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i> <i class="fa-solid fa-star"></i></div>',
						onRender: function() {}
					};
				},
				'nps-matrix': function(fieldData) {
					let chips = '';
					for (let i = 0; i <= 10; i++) {
						chips += '<span class="wlgf-nps-chip">' + i + '</span>';
					}
					return {
						field: '<div class="wlgf-preview-nps"><div class="wlgf-nps-chips">' + chips + '</div><div class="wlgf-text-muted small mt-1">(0 = Unlikely, 10 = Extremely Likely)</div></div>',
						onRender: function() {}
					};
				},
				'signature': function(fieldData) {
					const sigLabel = safeEscape(fieldData.label || 'Digital Signature Pad');
					return {
						field: '<div class="wlgf-signature-preview" style="border: 2px dashed #cbd5e1; border-radius: 8px; padding: 14px; text-align: center; background: #f8fafc;"><i class="fa-solid fa-pen-nib" style="font-size: 24px; color: #64748b;"></i><p style="margin: 6px 0 0 0; font-size: 13px; font-weight: 600; color: #334155;">' + sigLabel + ' (HTML5 Canvas Drawing Pad)</p></div>',
						onRender: function() {}
					};
				},
				'calculation': function(fieldData) {
					const label = safeEscape(fieldData.label || 'Estimated Total');
					const formula = safeEscape(fieldData.formula || '[field_1] * 10');
					const prefix = safeEscape(fieldData.prefix !== undefined ? fieldData.prefix : '$');
					const postfix = safeEscape(fieldData.postfix || '');
					return {
						field: '<div class="wlgf-preview-calc">' +
						       '<label class="form-label">' + label + '</label>' +
						       '<div class="wlgf-calc-badge"><i class="fa-solid fa-calculator"></i> <span class="wlgf-calc-val">' + prefix + '0.00' + postfix + '</span> <span class="wlgf-text-muted small">(' + formula + ')</span></div>' +
						       '</div>',
						onRender: function() {}
					};
				}
			},
			typeUserAttrs: {
				text: colWidthFieldAttr,
				number: colWidthFieldAttr,
				date: colWidthFieldAttr,
				file: Object.assign({}, colWidthFieldAttr, {
					allowed_types: {
						label: 'Allowed File Types (comma-separated)',
						type: 'text',
						value: 'pdf, doc, docx, png, jpg',
						description: 'Allowed extensions, e.g. pdf, doc, docx, png, jpg (leave blank for defaults)'
					},
					max_file_size: {
						label: 'Max File Size (in MB)',
						type: 'number',
						value: 10,
						description: 'Maximum upload file size limit in Megabytes (e.g. 10 for 10MB)'
					}
				}),
				textarea: colWidthFieldAttr,
				select: colWidthFieldAttr,
				'checkbox-group': colWidthFieldAttr,
				'radio-group': colWidthFieldAttr,
				'star-rating': colWidthFieldAttr,
				'nps-matrix': colWidthFieldAttr,
				terms: colWidthFieldAttr,
				button: colWidthFieldAttr,
				calculation: Object.assign({}, colWidthFieldAttr, {
					formula: {
						label: 'Calculation Formula (e.g. [quantity] * 25 + [addon])',
						type: 'text',
						value: ''
					},
					prefix: {
						label: 'Prefix / Currency Symbol (e.g. $)',
						type: 'text',
						value: '$'
					},
					postfix: {
						label: 'Postfix Label (e.g. /month)',
						type: 'text',
						value: ''
					},
					decimals: {
						label: 'Decimal Precision',
						type: 'number',
						value: 2
					}
				}),
				hidden: {
					utm_param: {
						label: 'Auto-Capture from URL Parameter (e.g. utm_source, gclid, ref)',
						type: 'text',
						value: ''
					}
				}
			},
			typeUserEvents: typeUserEventsConfig,
			typeUserDisabledAttrs: {
				file: ['placeholder'],
				date: ['step'],
				'divider': ['name', 'value', 'placeholder', 'access', 'required', 'description', 'inline', 'multiple', 'other', 'toggle'],
				'terms': ['access', 'inline', 'multiple', 'other', 'toggle'],
				'step-break': ['name', 'value', 'placeholder', 'access', 'required', 'description', 'inline', 'multiple'],
				'star-rating': ['placeholder', 'access', 'inline', 'multiple'],
				'nps-matrix': ['placeholder', 'access', 'inline', 'multiple'],
				'calculation': ['placeholder', 'access', 'inline', 'multiple', 'other', 'toggle']
			}
		};

		if (FormGenerator.FormIdSet === 'set' && FormGenerator.FormData) {
			fbOptions.formData = FormGenerator.FormData;
		}

		if (fbEditor) {
			formBuilder = $(fbEditor).formBuilder(fbOptions);

			// Sidebar Palette Quick-Search Filter (Sprint 2)
			setTimeout(function() {
				if ($('.cb-wrap').length && !$('.wlgf-palette-search-wrap').length) {
					$('.cb-wrap').prepend(
						'<div class="wlgf-palette-search-wrap">' +
						'<i class="fa-solid fa-magnifying-glass"></i>' +
						'<input type="text" class="wlgf-palette-search-input" placeholder="Search fields..." aria-label="Search fields">' +
						'</div>'
					);
				}
			}, 300);

			// Pro Field Palette Badging & Interception (Sprint 4 / Free Plan Security)
			if (!isPro) {
				const proFieldTypes = ['calculation', 'step-break', 'star-rating', 'nps-matrix', 'signature'];

				const checkAndBadgeProFields = function() {
					$('.cb-wrap ul li').each(function() {
						const $li = $(this);
						const dataType = $li.attr('data-type') || $li.data('type') || '';
						if (proFieldTypes.indexOf(dataType) !== -1) {
							if (!$li.find('.wlgf-palette-pro-pill').length) {
								$li.append('<span class="wlgf-palette-pro-pill">PRO</span>');
							}
							$li.addClass('wlgf-palette-pro-locked');
							try {
								if ($li.data('ui-draggable') || $li.data('draggable')) {
									$li.draggable('disable');
								}
							} catch (err) {}
						}
					});
				};
				setTimeout(checkAndBadgeProFields, 150);
				setTimeout(checkAndBadgeProFields, 400);
				setTimeout(checkAndBadgeProFields, 1000);
				setTimeout(checkAndBadgeProFields, 2000);

				function isProPaletteTarget(target) {
					if (!target) return null;
					const li = (typeof target.closest === 'function') ? target.closest('.cb-wrap ul li') : null;
					if (!li) return null;
					const type = li.getAttribute('data-type') || $(li).data('type') || '';
					if (proFieldTypes.indexOf(type) !== -1) {
						return { li: li, type: type };
					}
					return null;
				}

				function triggerProFieldModal(li, type, e) {
					if (e) {
						if (typeof e.preventDefault === 'function') e.preventDefault();
						if (typeof e.stopPropagation === 'function') e.stopPropagation();
						if (typeof e.stopImmediatePropagation === 'function') e.stopImmediatePropagation();
					}
					const rawLabel = ($(li).text() || '').replace('PRO', '').trim();
					const typeLabel = type ? type.replace(/-/g, ' ').replace(/\b\w/g, function(l){ return l.toUpperCase(); }) : 'Pro Field';
					const finalLabel = rawLabel || typeLabel;
					$('#wlgf-pro-modal-title').text('Unlock ' + finalLabel + ' (PRO Feature)');
					$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				}

				// Capture phase interception stops jQuery UI Draggable and formBuilder click before they start
				['mousedown', 'pointerdown', 'touchstart', 'click', 'dragstart'].forEach(function(evtName) {
					document.addEventListener(evtName, function(e) {
						const match = isProPaletteTarget(e.target);
						if (match) {
							triggerProFieldModal(match.li, match.type, e);
						}
					}, true);
				});

				// Scan canvas on load for existing forms that may contain PRO fields
				function flagCanvasProFields() {
					$('.frmb li.form-field').each(function() {
						const $f = $(this);
						const fType = $f.attr('type') || '';
						if (proFieldTypes.indexOf(fType) !== -1) {
							if (!$f.find('.wlgf-canvas-pro-locked-banner').length) {
								$f.addClass('wlgf-canvas-field--pro-locked');
								$f.prepend(
									'<div class="wlgf-canvas-pro-locked-banner">' +
									'<div class="wlgf-canvas-pro-locked-info"><i class="fa-solid fa-lock"></i> <strong>PRO Feature Locked:</strong> Upgrade to WLGF Pro to activate this field in live forms.</div>' +
									'<button type="button" class="wlgf-btn wlgf-btn--sm wlgf-btn--primary wlgf-canvas-unlock-pro-btn"><i class="fa-solid fa-crown"></i> Upgrade to Pro</button>' +
									'</div>'
								);
								$f.find('input, select, textarea').prop('disabled', true);
							}
						}
					});
				}
				setTimeout(flagCanvasProFields, 300);
				setTimeout(flagCanvasProFields, 800);
				setTimeout(flagCanvasProFields, 1600);

				$(document).on('click', '.wlgf-canvas-unlock-pro-btn, .wlgf-open-pro-modal-btn', function(e) {
					e.preventDefault();
					const isWizard = $(this).closest('#wlgf-wizard-accordion').length > 0;
					const isCondition = $(this).closest('#wlgf-conditions-accordion').length > 0;
					let title = 'Unlock Advanced PRO Features';
					if (isWizard) title = 'Unlock Multi-Step Form Wizard (PRO Feature)';
					else if (isCondition) title = 'Unlock Visual Conditional Logic (PRO Feature)';
					$('#wlgf-pro-modal-title').text(title);
					$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				});

				$(document).on('click', '.wlgf-pro-locked-section fieldset[disabled]', function(e) {
					if ($(e.target).closest('.wlgf-open-pro-modal-btn').length) return;
					const isWizard = $(this).closest('#wlgf-wizard-accordion').length > 0;
					const title = isWizard ? 'Unlock Multi-Step Form Wizard (PRO Feature)' : 'Unlock Visual Conditional Logic (PRO Feature)';
					$('#wlgf-pro-modal-title').text(title);
					$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				});

				$(document).on('change', '#wlgf-display-mode', function() {
					if (!isPro && $(this).val() !== 'inline') {
						$(this).val('inline');
						$('#wlgf-pro-modal-title').text('Unlock Popups, Slide-ins & Floating Widgets (PRO Feature)');
						$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
					}
				});
			}

			$(document).on('input keyup change', '.wlgf-palette-search-input', function() {
				const query = $(this).val().toLowerCase().trim();
				$('.cb-wrap ul li').each(function() {
					const text = $(this).text().toLowerCase();
					if (!query || text.indexOf(query) !== -1) {
						$(this).removeClass('wlgf-field-hidden');
					} else {
						$(this).addClass('wlgf-field-hidden');
					}
				});
			});

			// Modern Flatpickr initialization for canvas date fields
			function initCanvasFlatpickrs() {
				if (typeof flatpickr !== 'undefined') {
					$('.frmb input[type="date"]').each(function() {
						if (!this._flatpickr) {
							flatpickr(this, {
								dateFormat: 'Y-m-d',
								disableMobile: true,
								allowInput: true,
								minDate: $(this).attr('min') || 'today'
							});
						}
					});
				}
			}

			$(document).on('focusin click', '.frmb input[type="date"]', function() {
				if (typeof flatpickr !== 'undefined' && !this._flatpickr) {
					const fp = flatpickr(this, {
						dateFormat: 'Y-m-d',
						disableMobile: true,
						allowInput: true,
						minDate: $(this).attr('min') || 'today'
					});
					fp.open();
				}
			});

			setTimeout(initCanvasFlatpickrs, 400);
			setTimeout(initCanvasFlatpickrs, 1200);

			// Canvas Column Width Synchronization (Pillar 1)
			function syncCanvasColumnClasses() {
				$('.wlgf-field-col-bar').remove();
				$('.frmb li.form-field.button-field .tooltip-element, .frmb li.form-field.header-field .tooltip-element, .frmb li.form-field.paragraph-field .tooltip-element, .frmb li.form-field.divider-field .tooltip-element, .frmb li.form-field.step-break-field .tooltip-element').remove();
				$('.frmb li.form-field').each(function() {
					const $field = $(this);
					let colVal = $field.find('.column_width-wrap select, select[name="column_width"], .fld-column_width').val();
					if (!colVal) {
						const cls = $field.find('.className-wrap input, .fld-className').val() || '';
						const match = cls.match(/\bwlgf-col-(12|6|4|3)\b/);
						if (match) {
							colVal = match[1];
						}
					}
					colVal = colVal || '12';
					$field.removeClass('wlgf-canvas-col-12 wlgf-canvas-col-6 wlgf-canvas-col-4 wlgf-canvas-col-3')
					      .addClass('wlgf-canvas-col-' + colVal);
				});
				syncCanvasTooltips();
			}

			// Synchronize Tooltip Element Visibility Based on "Help Text" (description)
			function syncCanvasTooltips() {
				$('.frmb li.form-field.button-field .tooltip-element, .frmb li.form-field.header-field .tooltip-element, .frmb li.form-field.paragraph-field .tooltip-element, .frmb li.form-field.divider-field .tooltip-element, .frmb li.form-field.step-break-field .tooltip-element').remove();
				$('.frmb li.form-field').each(function() {
					const $field = $(this);
					const $tooltip = $field.find('.tooltip-element');
					const descInput = $field.find('.fld-description, input[name="description"]');
					let descVal = '';
					if (descInput.length) {
						descVal = (descInput.val() || '').trim();
					} else if ($tooltip.length) {
						descVal = ($tooltip.attr('tooltip') || '').trim();
					}

					if (!descVal || descVal === 'undefined' || descVal === 'null') {
						if ($tooltip.length) {
							$tooltip.attr('tooltip', '').addClass('wlgf-d-none').hide();
						}
					} else {
						if ($tooltip.length) {
							$tooltip.removeClass('wlgf-d-none').attr('tooltip', descVal).css('display', 'inline-flex').show();
						} else {
							const $label = $field.find('label.field-label').first();
							if ($label.length) {
								$('<span class="tooltip-element" tooltip="' + $('<div>').text(descVal).html() + '">?</span>').insertAfter($label);
							}
						}
					}
				});
			}

			$(document).on('input change keyup blur', '.frmb .fld-description, .frmb input[name="description"]', function() {
				const $field = $(this).closest('li.form-field');
				const val = ($(this).val() || '').trim();
				let $tooltip = $field.find('.tooltip-element');
				if (!val) {
					$tooltip.attr('tooltip', '').addClass('wlgf-d-none').hide();
				} else {
					if (!$tooltip.length) {
						const $label = $field.find('label.field-label').first();
						if ($label.length) {
							$tooltip = $('<span class="tooltip-element" tooltip="' + $('<div>').text(val).html() + '">?</span>');
							$tooltip.insertAfter($label);
						}
					} else {
						$tooltip.removeClass('wlgf-d-none').attr('tooltip', val).css('display', 'inline-flex').show();
					}
				}
			});

			$(document).on('change input', '.frmb .column_width-wrap select, .frmb select[name="column_width"], .frmb .fld-column_width', function() {
				const rawVal = String($(this).val() || '12');
				if (!isPro && rawVal.indexOf('pro_') === 0) {
					$(this).val('12');
					$('#wlgf-pro-modal-title').text('Multi-Column Grid (50%, 33%, 25%) is a Pro Feature');
					$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
					return;
				}
				const colVal = $(this).val() || '12';
				const $field = $(this).closest('li.form-field');
				$field.removeClass('wlgf-canvas-col-12 wlgf-canvas-col-6 wlgf-canvas-col-4 wlgf-canvas-col-3')
				       .addClass('wlgf-canvas-col-' + colVal);
			});

			$(document).on('click', '.frmb .style-wrap button, .frmb .style-wrap input', function() {
				setTimeout(syncCanvasButtonStyles, 60);
			});

			$(document).on('input change', '.frmb .className-wrap input, .frmb input[name="className"], .frmb .fld-className', function() {
				syncCanvasButtonStyles();
			});

			$(document).on('click', '.cb-wrap li, .frmb .del-button, .frmb .copy-button', function() {
				setTimeout(syncCanvasColumnClasses, 150);
				setTimeout(syncCanvasButtonStyles, 200);
				setTimeout(syncCanvasTooltips, 250);
				setTimeout(syncFieldOptionsUi, 260);
				setTimeout(syncCanvasColumnClasses, 450);
				setTimeout(syncCanvasButtonStyles, 500);
				setTimeout(syncCanvasTooltips, 550);
				setTimeout(syncFieldOptionsUi, 560);
			});

			// Options & Choices UI Synchronization (Single Clean Input by default, Separate Values toggle)
			function syncFieldOptionsUi() {
				$('.frmb .field-options').each(function() {
					const $fieldOptions = $(this);

					// 1. Title Row with 'Separate Values' toggle
					let $titleRow = $fieldOptions.find('.wlgf-options-title-row');
					if (!$titleRow.length) {
						const $origLabel = $fieldOptions.children('label:not(.wlgf-options-toggle-values-wrap)');
						let headingText = 'Options';
						if ($origLabel.length) {
							const txt = $origLabel.first().text().trim();
							if (txt) headingText = txt;
							$origLabel.hide();
						}

						$titleRow = $(
							'<div class="wlgf-options-title-row">' +
								'<span class="wlgf-options-heading">' + headingText + '</span>' +
								'<label class="wlgf-options-toggle-values-wrap" title="Show separate internal values for logic, integrations and calculations">' +
									'<input type="checkbox" class="wlgf-toggle-values-checkbox"> ' +
									'<span>Separate Values</span>' +
								'</label>' +
							'</div>'
						);
						$fieldOptions.prepend($titleRow);
					}

					// 2. Table Column Headers
					if (!$fieldOptions.find('.wlgf-options-table-header').length) {
						const $tableHeader = $(
							'<div class="wlgf-options-table-header">' +
								'<span class="wlgf-opt-th-default" title="Default Selected">Default</span>' +
								'<span class="wlgf-opt-th-label">Option Label (Display Text)</span>' +
								'<span class="wlgf-opt-th-value">Option Value (Stored)</span>' +
								'<span class="wlgf-opt-th-action"></span>' +
							'</div>'
						);
						const $sortable = $fieldOptions.find('.sortable-options, .sortable-options-wrap');
						if ($sortable.length) {
							$tableHeader.insertBefore($sortable.first());
						} else {
							$titleRow.after($tableHeader);
						}
					}

					// 3. Detect if existing options have customized/differing stored values
					const $toggleCheckbox = $fieldOptions.find('.wlgf-toggle-values-checkbox');
					let shouldShowValues = $fieldOptions.hasClass('wlgf-show-values') || $toggleCheckbox.is(':checked');

					if (!shouldShowValues) {
						$fieldOptions.find('.sortable-options > li').each(function() {
							const $lbl = $(this).find('input.option-label');
							const $val = $(this).find('input.option-value');
							if ($lbl.length && $val.length) {
								const l = ($lbl.val() || '').trim();
								const v = ($val.val() || '').trim();
								if (l && v && l.toLowerCase() !== v.toLowerCase() && !v.match(/^option-\d+$/i)) {
									shouldShowValues = true;
								}
							}
						});
					}

					if (shouldShowValues) {
						$fieldOptions.addClass('wlgf-show-values');
						$toggleCheckbox.prop('checked', true);
					} else {
						$fieldOptions.removeClass('wlgf-show-values');
						$toggleCheckbox.prop('checked', false);
					}

					// 4. Set placeholders and keep values synced
					$fieldOptions.find('.sortable-options > li').each(function(idx) {
						const $li = $(this);
						const $lbl = $li.find('input.option-label');
						const $val = $li.find('input.option-value');
						if ($lbl.length && !$lbl.attr('placeholder')) {
							$lbl.attr('placeholder', 'Option ' + (idx + 1));
						}
						if ($val.length && !$val.attr('placeholder')) {
							$val.attr('placeholder', 'Value ' + (idx + 1));
						}
						// If in single-mode and value is default 'option-X' or empty, sync from label
						if (!shouldShowValues && $lbl.length && $val.length) {
							const lVal = $lbl.val() || '';
							const vVal = $val.val() || '';
							if ((!vVal || vVal.match(/^option-\d+$/i)) && lVal) {
								$val.val(lVal);
							}
						}
					});
				});
			}

			// Debounced schedule for options sync
			let syncFieldOptionsTimer = null;
			function scheduleSyncFieldOptionsUi() {
				clearTimeout(syncFieldOptionsTimer);
				syncFieldOptionsTimer = setTimeout(syncFieldOptionsUi, 40);
			}

			// Toggle 'Separate Values' checkbox
			$(document).on('change', '.wlgf-toggle-values-checkbox', function() {
				const isChecked = $(this).is(':checked');
				const $fieldOptions = $(this).closest('.field-options');
				if (isChecked) {
					$fieldOptions.addClass('wlgf-show-values');
					$fieldOptions.find('.sortable-options > li').each(function() {
						const $lbl = $(this).find('input.option-label');
						const $val = $(this).find('input.option-value');
						if ($lbl.length && $val.length) {
							const l = ($lbl.val() || '').trim();
							const v = ($val.val() || '').trim();
							if ((!v || v.match(/^option-\d+$/i)) && l) {
								$val.val(l).trigger('change');
							}
						}
					});
				} else {
					$fieldOptions.removeClass('wlgf-show-values');
					$fieldOptions.find('.sortable-options > li').each(function() {
						const $lbl = $(this).find('input.option-label');
						const $val = $(this).find('input.option-value');
						if ($lbl.length && $val.length) {
							const l = ($lbl.val() || '').trim();
							if (l) {
								$val.val(l).trigger('change');
							}
						}
					});
				}
			});

			// Auto-sync label to value when in single-input mode
			$(document).on('input', '.frmb .sortable-options input.option-label', function() {
				const $li = $(this).closest('li');
				const $fieldOptions = $(this).closest('.field-options');
				const $val = $li.find('input.option-value');
				if (!$fieldOptions.hasClass('wlgf-show-values') && $val.length) {
					$val.val($(this).val()).trigger('change');
				}
			});

			// Add Option / Field Interaction Event Watchers
			$(document).on('click', '.frmb .field-options .option-actions a, .frmb .field-options .add-opt, .frmb .field-options .add', function() {
				setTimeout(syncFieldOptionsUi, 50);
				setTimeout(syncFieldOptionsUi, 180);
			});

			function syncRequiredAsterisks() {
				$('.frmb li.form-field').each(function() {
					const $field = $(this);
					const isDisplayOnly = $field.is('.button-field, .header-field, .paragraph-field, .divider-field, .step-break-field');
					const $asterisk = $field.find('.required-asterisk');
					if (isDisplayOnly) {
						$asterisk.attr('style', 'display: none !important;');
						return;
					}
					const $reqInput = $field.find('input.fld-required');
					if ($reqInput.length) {
						if ($reqInput.is(':checked')) {
							$asterisk.attr('style', 'display: inline-block !important;');
						} else {
							$asterisk.attr('style', 'display: none !important;');
						}
					}
				});
			}

			$(document).on('change click', '.frmb input.fld-required', function() {
				const $field = $(this).closest('.form-field');
				const $asterisk = $field.find('.required-asterisk');
				if ($(this).is(':checked')) {
					$asterisk.attr('style', 'display: inline-block !important;');
				} else {
					$asterisk.attr('style', 'display: none !important;');
				}
			});

			$(document).on('click', '.frmb li.form-field, .frmb .toggle-form', function() {
				setTimeout(syncFieldOptionsUi, 60);
				setTimeout(syncRequiredAsterisks, 70);
			});

			// MutationObserver to catch dynamic DOM changes from formBuilder
			if (window.MutationObserver && fbEditor) {
				const optionsObserver = new MutationObserver(function(mutations) {
					for (let i = 0; i < mutations.length; i++) {
						if (mutations[i].addedNodes && mutations[i].addedNodes.length) {
							scheduleSyncFieldOptionsUi();
							setTimeout(syncRequiredAsterisks, 50);
							break;
						}
					}
				});
				optionsObserver.observe(fbEditor, { childList: true, subtree: true });
			}

			if (formBuilder && formBuilder.promise) {
				formBuilder.promise.then(function() {
					setTimeout(syncCanvasColumnClasses, 300);
					setTimeout(syncCanvasButtonStyles, 350);
					setTimeout(syncCanvasTooltips, 400);
					setTimeout(syncFieldOptionsUi, 450);
					setTimeout(syncRequiredAsterisks, 500);
					setTimeout(syncCanvasColumnClasses, 800);
					setTimeout(syncCanvasButtonStyles, 850);
					setTimeout(syncCanvasTooltips, 900);
					setTimeout(syncFieldOptionsUi, 950);
					setTimeout(syncRequiredAsterisks, 1000);
				});
			}
		}

		// Generic Collapsible Accordion Toggle
		$('.wlgf-collapsible__header').on('click', function() {
			$(this).closest('.wlgf-collapsible').toggleClass('wlgf-collapsible--active');
			if ($(this).closest('#wlgf-conditions-accordion').hasClass('wlgf-collapsible--active')) {
				renderConditionsUI();
			}

			// Update toggle all button state if inside tips accordion
			if ($(this).closest('.wlgf-field-tips-accordion').length) {
				const $items = $('.wlgf-field-tips-accordion .wlgf-field-tip-item');
				const allOpen = $items.length && $items.filter('.wlgf-collapsible--active').length === $items.length;
				$('#wlgf-toggle-all-tips').html(allOpen ? '<i class="fa-solid fa-angles-up"></i> Collapse All' : '<i class="fa-solid fa-angles-down"></i> Expand All');
			}
		});

		// Expand / Collapse All Tips Field Guides Toggle
		$('#wlgf-toggle-all-tips').on('click', function(e) {
			e.preventDefault();
			const $items = $('.wlgf-field-tips-accordion .wlgf-field-tip-item');
			const allOpen = $items.length && $items.filter('.wlgf-collapsible--active').length === $items.length;
			if (allOpen) {
				$items.removeClass('wlgf-collapsible--active');
				$(this).html('<i class="fa-solid fa-angles-down"></i> Expand All');
			} else {
				$items.addClass('wlgf-collapsible--active');
				$(this).html('<i class="fa-solid fa-angles-up"></i> Collapse All');
			}
		});

		// Theme Styler Comprehensive Real-Time Canvas Sync
		function syncThemeStylerToCanvas() {
			const hex = $('#wlgf-theme-primary').val() || '#2563eb';
			const radius = $('#wlgf-theme-radius').val() || '6px';
			const bg = $('#wlgf-theme-bg').val() || 'card';
			const font = $('#wlgf-theme-font').val() || 'inherit';
			const btnWidth = $('#wlgf-theme-btn-width').val() || 'auto';
			const fieldSize = $('#wlgf-theme-field-size').val() || 'default';
			const mode = $('#wlgf-theme-mode').val() || 'light';
			const inputStyle = $('#wlgf-theme-input-style').val() || 'bordered';
			const maxWidth = $('#wlgf-theme-max-width').val() || '640px';
			const btnShadow = $('#wlgf-theme-btn-shadow').val() || 'default';
			const labelStyle = $('#wlgf-theme-label-style').val() || 'default';

			$('#wlgf-dynamic-styler-tag').remove();

			let fontRule = (font && font !== 'inherit') ? 'font-family: ' + font + ' !important;' : '';
			let bgRule = '';
			if (bg === 'card') {
				bgRule = '.frmb, .form-wrap .stage-wrap { background: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: ' + radius + ' !important; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important; padding: 20px !important; }';
			}

			// Button width rule
			let btnWidthRule = '';
			if (btnWidth === 'full') {
				btnWidthRule = '.frmb li.form-field.button-field .formbuilder-button, .frmb li.form-field.button-field .prev-holder { display: block !important; width: 100% !important; } .frmb li.form-field.button-field button:not(.style-wrap button) { width: 100% !important; display: flex !important; }';
			} else if (btnWidth === 'center') {
				btnWidthRule = '.frmb li.form-field.button-field .formbuilder-button, .frmb li.form-field.button-field .prev-holder { text-align: center !important; } .frmb li.form-field.button-field button:not(.style-wrap button) { margin: 0 auto !important; }';
			}

			// Field Size Density
			let sizeRule = '';
			if (fieldSize === 'compact') {
				sizeRule = '.frmb .form-control:not(select), .frmb input[type="text"], .frmb input[type="email"], .frmb input[type="number"], .frmb input[type="tel"], .frmb input[type="date"] { padding: 6px 10px !important; font-size: 13px !important; min-height: 36px !important; } .frmb select { padding: 0 30px 0 10px !important; font-size: 13px !important; height: 36px !important; min-height: 36px !important; max-height: 36px !important; line-height: normal !important; vertical-align: middle !important; } .frmb button:not(.style-wrap button) { padding: 6px 14px !important; font-size: 13px !important; min-height: 36px !important; }';
			} else if (fieldSize === 'large') {
				sizeRule = '.frmb .form-control:not(select), .frmb input[type="text"], .frmb input[type="email"], .frmb input[type="number"], .frmb input[type="tel"], .frmb input[type="date"] { padding: 12px 18px !important; font-size: 16px !important; min-height: 52px !important; } .frmb select { padding: 0 38px 0 16px !important; font-size: 16px !important; height: 52px !important; min-height: 52px !important; max-height: 52px !important; line-height: normal !important; vertical-align: middle !important; } .frmb button:not(.style-wrap button) { padding: 12px 28px !important; font-size: 16px !important; min-height: 52px !important; }';
			}

			// Dark Mode is previewed in Live Preview Modal and on the frontend
			let modeRule = '';

			// Input Style
			let inputStyleRule = '';
			if (inputStyle === 'filled') {
				inputStyleRule = '.frmb .form-control, .frmb input[type="text"], .frmb input[type="email"], .frmb input[type="number"], .frmb input[type="tel"], .frmb input[type="date"], .frmb select, .frmb textarea { background-color: #f1f5f9 !important; border-color: transparent !important; } .frmb .form-control:focus, .frmb input:focus, .frmb select:focus, .frmb textarea:focus { background-color: #ffffff !important; border-color: ' + hex + ' !important; }';
			} else if (inputStyle === 'underline') {
				inputStyleRule = '.frmb .form-control, .frmb input[type="text"], .frmb input[type="email"], .frmb input[type="number"], .frmb input[type="tel"], .frmb input[type="date"], .frmb select, .frmb textarea { border-top: none !important; border-left: none !important; border-right: none !important; border-bottom: 2px solid #cbd5e1 !important; border-radius: 0 !important; background-color: transparent !important; }';
			}

			// Button Shadow
			let btnShadowRule = '';
			if (btnShadow === 'glow') {
				let fullHex = hex;
				if (/^#[0-9a-fA-F]{3}$/.test(fullHex)) {
					fullHex = '#' + fullHex[1] + fullHex[1] + fullHex[2] + fullHex[2] + fullHex[3] + fullHex[3];
				}
				btnShadowRule = '.frmb button.btn-primary:not(.style-wrap button) { box-shadow: 0 4px 14px ' + fullHex + '66 !important; }';
			} else if (btnShadow === 'flat') {
				btnShadowRule = '.frmb button:not(.style-wrap button) { box-shadow: none !important; }';
			}

			// Label Style
			let labelStyleRule = '';
			if (labelStyle === 'bold') {
				labelStyleRule = '.frmb .field-label, .frmb label { font-weight: 700 !important; }';
			} else if (labelStyle === 'uppercase') {
				labelStyleRule = '.frmb .field-label, .frmb label { text-transform: uppercase !important; font-size: 11px !important; letter-spacing: 0.06em !important; font-weight: 700 !important; }';
			}

			// Form Max Width
			let maxWidthRule = '';
			if (maxWidth && maxWidth !== '100%') {
				maxWidthRule = '.form-wrap .stage-wrap { max-width: ' + maxWidth + ' !important; margin: 0 auto !important; }';
			}

			const css = '.wlgf-builder-canvas, .frmb, .stage-wrap, .wlgf-modal--preview, .wlgf-preview-body, #wlgf-preview-render-mount, .wlgf-preview-field { ' +
			            '--wlgf-primary: ' + hex + ' !important; ' +
			            '--wlgf-radius: ' + radius + ' !important; ' +
			            fontRule +
			            '}' +
			            '.frmb button:not(.style-wrap button), .frmb input[type="button"], .frmb input[type="submit"], .frmb .form-control, .frmb input[type="text"], .frmb input[type="email"], .frmb input[type="number"], .frmb input[type="tel"], .frmb input[type="date"], .frmb select, .frmb textarea, .wlgf-preview-field input:not([type="radio"]):not([type="checkbox"]), .wlgf-preview-field select, .wlgf-preview-field textarea, .wlgf-preview-field button { border-radius: ' + radius + ' !important; }' +
			            '.frmb button.btn-primary:not(.style-wrap button), .frmb .style-wrap button.btn-primary, .wlgf-preview-field .btn-primary, .wlgf-preview-field .wlgf-btn--primary { background-color: ' + hex + ' !important; border-color: ' + hex + ' !important; color: #ffffff !important; }' +
			            bgRule +
			            btnWidthRule +
			            sizeRule +
			            modeRule +
			            inputStyleRule +
			            btnShadowRule +
			            labelStyleRule +
			            maxWidthRule;

			$('head').append('<style id="wlgf-dynamic-styler-tag">' + css + '</style>');
		}

		// Color Picker & Hex Input Sync
		$('#wlgf-theme-primary-picker').on('input change', function() {
			const val = $(this).val();
			$('#wlgf-theme-primary').val(val);
			syncThemeStylerToCanvas();
		});

		$('#wlgf-theme-primary').on('input change', function() {
			const hex = $(this).val().trim();
			if (/^#[0-9A-Fa-f]{6}$/.test(hex)) {
				$('#wlgf-theme-primary-picker').val(hex);
				syncThemeStylerToCanvas();
			}
		});

		$('.wlgf-theme-swatch').on('click', function() {
			const color = $(this).data('color');
			if (color) {
				$('#wlgf-theme-primary').val(color);
				$('#wlgf-theme-primary-picker').val(color);
				syncThemeStylerToCanvas();
			}
		});

		$('#wlgf-theme-radius, #wlgf-theme-bg, #wlgf-theme-font, #wlgf-theme-btn-width, #wlgf-theme-field-size, #wlgf-theme-mode, #wlgf-theme-input-style, #wlgf-theme-max-width, #wlgf-theme-btn-shadow, #wlgf-theme-label-style').on('change', function() {
			syncThemeStylerToCanvas();
		});

		// Trigger initial styling sync on load
		setTimeout(syncThemeStylerToCanvas, 100);

		// Confirmation Type Switcher
		$('#wlgf-confirmation-type').on('change', function() {
			if ($(this).val() === 'redirect') {
				$('#wlgf-redirect-url-wrap').removeClass('wlgf-d-none');
				$('#wlgf-custom-message-wrap').addClass('wlgf-d-none');
			} else {
				$('#wlgf-redirect-url-wrap').addClass('wlgf-d-none');
				$('#wlgf-custom-message-wrap').removeClass('wlgf-d-none');
			}
		});

		// Auto-Responder Toggle Switcher
		$('#wlgf-auto-reply-enabled').on('change', function() {
			if (this.checked) {
				$('#wlgf-auto-reply-fields').removeClass('wlgf-d-none');
				$(this).closest('.wlgf-toggle-card').addClass('wlgf-toggle-card--active');

				const $subj = $('#wlgf-auto-reply-subject');
				const $body = $('#wlgf-auto-reply-body');
				if (typeof FormGenerator !== 'undefined') {
					if (!$.trim($subj.val()) && FormGenerator.DefaultReplySubj) {
						$subj.val(FormGenerator.DefaultReplySubj);
					}
					if (!$.trim($body.val()) && FormGenerator.DefaultReplyBody) {
						$body.val(FormGenerator.DefaultReplyBody);
					}
				}
			} else {
				$('#wlgf-auto-reply-fields').addClass('wlgf-d-none');
				$(this).closest('.wlgf-toggle-card').removeClass('wlgf-toggle-card--active');
			}
		});

		// Merge Tag Inserter
		$(document).on('click', '.wlgf-tag-pill, .wlgf-tag-badge', function() {
			const tag = $(this).data('tag') || $(this).text().trim();
			const $textarea = $('#wlgf-auto-reply-body');
			const cursorPos = $textarea.prop('selectionStart') || $textarea.val().length;
			const textBefore = $textarea.val().substring(0, cursorPos);
			const textAfter = $textarea.val().substring(cursorPos);

			$textarea.val(textBefore + tag + textAfter);
			$textarea.focus();
			$textarea.prop('selectionStart', cursorPos + tag.length);
			$textarea.prop('selectionEnd', cursorPos + tag.length);
		});

		// Template Modal Open / Close & Live Search / Filtering
		function filterTemplates() {
			const activeCat = $('.wlgf-template-filter-pill.active').data('category') || 'all';
			const searchVal = ($('#wlgf-template-search-input').val() || '').toLowerCase().trim();
			let matchCount = 0;

			$('#wlgf-templates-grid .wlgf-template-card').each(function() {
				const $card = $(this);
				const cardCat = $card.data('category');
				const cardKeywords = ($card.data('keywords') || '').toLowerCase();
				const cardTitle = $card.find('.wlgf-template-card__title').text().toLowerCase();
				const cardDesc = $card.find('.wlgf-template-card__desc').text().toLowerCase();
				const cardFullText = cardTitle + ' ' + cardDesc + ' ' + cardKeywords;

				const matchesCat = (activeCat === 'all' || cardCat === activeCat || cardCat === 'all');
				const matchesSearch = !searchVal || cardFullText.indexOf(searchVal) !== -1;

				if (matchesCat && matchesSearch) {
					$card.show();
					matchCount++;
				} else {
					$card.hide();
				}
			});

			$('#wlgf-templates-grid .wlgf-template-empty-search').remove();
			if (matchCount === 0) {
				$('#wlgf-templates-grid').append('<div class="wlgf-template-empty-search"><i class="fa-solid fa-folder-open"></i>No templates found matching your search. Try different keywords or switch category filter.</div>');
			}
		}

		function openTemplatesModal() {
			$('.wlgf-template-filter-pill').removeClass('active');
			$('.wlgf-template-filter-pill[data-category="all"]').addClass('active');
			$('#wlgf-template-search-input').val('');
			filterTemplates();
			$('#wlgf-templates-modal')
				.css('display', 'flex')
				.removeClass('wlgf-d-none')
				.addClass('wlgf-modal-overlay--active');
			$('body').addClass('wlgf-modal-open');
		}

		function closeTemplatesModal() {
			$('#wlgf-templates-modal')
				.removeClass('wlgf-modal-overlay--active')
				.addClass('wlgf-d-none')
				.css('display', 'none');
			$('body').removeClass('wlgf-modal-open');
		}

		$(document).on('click', '#wlgf-open-templates-btn, .wlgf-open-templates-shortcut-btn', function(e) {
			e.preventDefault();
			openTemplatesModal();
		});

		$(document).on('click', '#wlgf-close-templates-btn', function(e) {
			e.preventDefault();
			e.stopPropagation();
			closeTemplatesModal();
		});

		$(document).on('click', '#wlgf-templates-modal', function(e) {
			if (e.target === this) {
				closeTemplatesModal();
			}
		});

		$(document).on('click', '.wlgf-template-filter-pill', function(e) {
			e.preventDefault();
			$('.wlgf-template-filter-pill').removeClass('active');
			$(this).addClass('active');
			filterTemplates();
		});

		$(document).on('input keyup', '#wlgf-template-search-input', function() {
			filterTemplates();
		});

		// Consolidated, Bulletproof Template Apply Handler
		$(document).on('click', '.wlgf-template-card__btn, .wlgf-template-card', function(e) {
			e.preventDefault();
			e.stopPropagation();

			const $card = $(this).closest('.wlgf-template-card');
			const templateKey = $card.data('template');
			if (!templateKey) {
				closeTemplatesModal();
				return;
			}

			const isCardPro = ($card.data('is-pro') == 1 || $card.attr('data-is-pro') === '1');
			if (isCardPro && !isPro) {
				const $clone = $card.find('.wlgf-template-card__title').clone();
				$clone.find('.wlgf-template-card__title-num').remove();
				const templateTitle = $clone.text().trim() || $card.find('.wlgf-template-card__title').text().trim() || 'Pro Template';
				$('#wlgf-pro-modal-title').text('Unlock ' + templateTitle);
				closeTemplatesModal();
				$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active').css('display', 'flex');
				return;
			}

			if (templateKey === 'blank') {
				let currentFields = [];
				if (formBuilder && formBuilder.actions && typeof formBuilder.actions.getData === 'function') {
					try { currentFields = formBuilder.actions.getData() || []; } catch(err){}
				}
				const doApplyBlank = function() {
					if (formBuilder && formBuilder.actions) {
						try { formBuilder.actions.clearFields(); } catch(err){}
						try { formBuilder.actions.setData([]); } catch(err){}
					}
					$('#wlgf-form-name').val('Untitled Form');
					closeTemplatesModal();
					if (typeof renderConditionsUI === 'function') {
						try { renderConditionsUI(); } catch(err){}
					}
				};

				if (currentFields.length > 0) {
					if (typeof wlgfConfirm === 'function') {
						wlgfConfirm({
							title: 'Start with a Blank Form?',
							message: 'Starting with a blank form will clear all current fields from your canvas. Are you sure you want to proceed?',
							confirmText: 'Yes, Clear Canvas',
							cancelText: 'Keep Current Form',
							type: 'warning'
						}, function(confirmed) {
							if (confirmed) doApplyBlank();
						});
					} else {
						if (confirm('Start with a blank form? This will clear all current fields from your canvas.')) {
							doApplyBlank();
						}
					}
					return;
				}
				doApplyBlank();
				return;
			}

			// Alias resolution (e.g. realestate vs real_estate, quote vs cost_estimator)
			let template = formTemplates[templateKey];
			if (!template) {
				if (templateKey === 'realestate') template = formTemplates.real_estate || formTemplates.realestate;
				else if (templateKey === 'real_estate') template = formTemplates.realestate || formTemplates.real_estate;
				else if (templateKey === 'quote') template = formTemplates.cost_estimator || formTemplates.quote;
				else if (templateKey === 'cost_estimator') template = formTemplates.quote || formTemplates.cost_estimator;
			}

			if (!template || !template.data) {
				if (typeof wlgfAlert === 'function') {
					wlgfAlert({
						title: 'Template Error',
						message: 'Template data could not be loaded.',
						type: 'danger'
					});
				} else {
					alert('Template data could not be loaded.');
				}
				closeTemplatesModal();
				return;
			}

			let currentFields = [];
			if (formBuilder && formBuilder.actions && typeof formBuilder.actions.getData === 'function') {
				try { currentFields = formBuilder.actions.getData() || []; } catch(err){}
			}

			const doApplyTemplate = function() {
				if (formBuilder && formBuilder.actions) {
					try {
						let templateData = template.data || [];
						if (!isPro) {
							const proFieldTypes = ['calculation', 'step-break', 'star-rating', 'nps-matrix', 'signature'];
							templateData = templateData.filter(function(item) {
								return proFieldTypes.indexOf(item.type) === -1;
							});
						}
						formBuilder.actions.setData(templateData);
						$('#wlgf-form-name').val(template.name);
						if (typeof syncCanvasColumnClasses === 'function') {
							setTimeout(syncCanvasColumnClasses, 100);
							setTimeout(syncCanvasColumnClasses, 400);
						}
						if (typeof syncCanvasButtonStyles === 'function') {
							setTimeout(syncCanvasButtonStyles, 150);
						}
					} catch(err) {
						console.error('Error applying template:', err);
					}
				}

				if (template.payment_settings) {
					const ps = template.payment_settings;
					if (ps.payment_enabled) {
						$('#wlgf-payment-enabled').prop('checked', true).trigger('change');
						if (ps.payment_type) {
							$('#wlgf-payment-type').val(ps.payment_type).trigger('change');
						}
						if (ps.payment_calc_field) {
							$('#wlgf-payment-calc-field').data('selected', ps.payment_calc_field);
							setTimeout(function() {
								if (typeof updatePaymentCalcDropdown === 'function') {
									updatePaymentCalcDropdown();
								}
								$('#wlgf-payment-calc-field').val(ps.payment_calc_field);
							}, 350);
						}
						if (ps.payment_item_name) {
							$('#wlgf-payment-item-name').val(ps.payment_item_name);
						}
					}
				}

				closeTemplatesModal();
				if (typeof renderConditionsUI === 'function') {
					try { renderConditionsUI(); } catch(err){}
				}
			};

			if (currentFields.length > 0) {
				if (typeof wlgfConfirm === 'function') {
					wlgfConfirm({
						title: 'Replace Current Form Fields?',
						message: 'This will replace your current canvas fields with the <strong>"' + $('<div>').text(template.name).html() + '"</strong> template. Continue?',
						confirmText: 'Yes, Apply Template',
						cancelText: 'Keep Current Form',
						type: 'warning',
						icon: 'fa-solid fa-wand-magic-sparkles'
					}, function(confirmed) {
						if (confirmed) doApplyTemplate();
					});
				} else {
					if (confirm('This will replace your current canvas fields with the "' + template.name + '" template. Continue?')) {
						doApplyTemplate();
					}
				}
				return;
			}
			doApplyTemplate();
		});

		// Pro Feature Modal Close Handlers
		$(document).on('click', '#wlgf-close-pro-modal-btn', function(e) {
			e.preventDefault();
			$('#wlgf-pro-feature-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
		});

		$(document).on('click', '#wlgf-pro-feature-modal', function(e) {
			if (e.target === this) {
				$('#wlgf-pro-feature-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
			}
		});

		// ==========================================
		// Visual Conditional Logic Manager (PRO-001)
		// ==========================================
		let formConditions = (FormGenerator.Conditions && typeof FormGenerator.Conditions === 'object' && !Array.isArray(FormGenerator.Conditions)) ? FormGenerator.Conditions : {};

		const conditionOperators = [
			{ value: 'equals', label: 'Equals' },
			{ value: 'not_equals', label: 'Does Not Equal' },
			{ value: 'contains', label: 'Contains' },
			{ value: 'not_contains', label: 'Does Not Contain' },
			{ value: 'greater_than', label: 'Greater Than (>)' },
			{ value: 'less_than', label: 'Less Than (<)' },
			{ value: 'is_empty', label: 'Is Empty' },
			{ value: 'is_not_empty', label: 'Is Not Empty' }
		];

		function getCanvasFieldList() {
			const fields = [];
			if (formBuilder && typeof formBuilder.actions.getData === 'function') {
				const data = formBuilder.actions.getData();
				if (Array.isArray(data)) {
					data.forEach(function(f) {
						if (f.name && f.type !== 'header' && f.type !== 'paragraph' && f.type !== 'button' && f.type !== 'step-break') {
							fields.push({
								name: f.name,
								label: f.label || f.name,
								type: f.type
							});
						}
					});
				}
			}
			return fields;
		}

		function renderConditionsUI() {
			const $container = $('#wlgf-conditions-container');
			$container.empty();

			if (!isPro) {
				$container.html('<div style="padding: 16px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; text-align: center; color: #64748b; font-size: 13px;"><i class="fa-solid fa-lock" style="font-size: 18px; margin-bottom: 6px; display: block; color: #f59e0b;"></i><strong>Visual Conditional Logic is a PRO Feature</strong><p style="margin: 4px 0 0 0;">Conditional logic rules are disabled in the Free edition. Upgrade to Lead Generation Form Pro to activate smart branching.</p></div>');
				return;
			}

			const availableFields = getCanvasFieldList();

			if (!Object.keys(formConditions).length) {
				$container.html('<p class="wlgf-help-text wlgf-text-muted">No conditional logic rules defined yet. Click below to add your first rule.</p>');
				return;
			}

			Object.keys(formConditions).forEach(function(targetField) {
				const cond = formConditions[targetField];
				if (!cond || !Array.isArray(cond.rules)) return;

				const $card = $('<div class="wlgf-condition-card" data-target-field="' + safeEscape(targetField) + '"></div>');
				
				let headerHtml = '<div class="wlgf-condition-card__header">';
				headerHtml += '<div class="wlgf-flex wlgf-items-center wlgf-gap-2 wlgf-flex-wrap">';
				headerHtml += '<select class="wlgf-select wlgf-select--sm wlgf-condition-target">';
				availableFields.forEach(function(f) {
					headerHtml += '<option value="' + safeEscape(f.name) + '"' + (f.name === targetField ? ' selected' : '') + '>' + safeEscape(f.label || f.name) + ' (' + safeEscape(f.name) + ')</option>';
				});
				headerHtml += '</select>';
				headerHtml += '<select class="wlgf-select wlgf-select--sm wlgf-select--auto wlgf-condition-action">';
				headerHtml += '<option value="show"' + (cond.action === 'show' ? ' selected' : '') + '>Show this field when</option>';
				headerHtml += '<option value="hide"' + (cond.action === 'hide' ? ' selected' : '') + '>Hide this field when</option>';
				headerHtml += '</select>';
				headerHtml += '<select class="wlgf-select wlgf-select--sm wlgf-select--auto wlgf-condition-logic">';
				headerHtml += '<option value="all"' + (cond.logic === 'all' ? ' selected' : '') + '>ALL rules match (AND)</option>';
				headerHtml += '<option value="any"' + (cond.logic === 'any' ? ' selected' : '') + '>ANY rule matches (OR)</option>';
				headerHtml += '</select>';
				headerHtml += '</div>';
				headerHtml += '<button type="button" class="wlgf-btn wlgf-btn--danger wlgf-btn--sm wlgf-remove-condition-btn" title="Remove Condition"><i class="fa-solid fa-trash-can"></i></button>';
				headerHtml += '</div>';

				$card.append(headerHtml);

				const $rulesList = $('<div class="wlgf-condition-rules-list"></div>');
				cond.rules.forEach(function(rule, rIndex) {
					const $row = $('<div class="wlgf-condition-rule-row" data-rule-index="' + rIndex + '"></div>');

					// Source field select
					let srcSelect = '<select class="wlgf-select wlgf-select--sm wlgf-rule-source">';
					availableFields.forEach(function(f) {
						srcSelect += '<option value="' + safeEscape(f.name) + '"' + (f.name === rule.field ? ' selected' : '') + '>' + safeEscape(f.label || f.name) + ' (' + safeEscape(f.name) + ')</option>';
					});
					srcSelect += '</select>';
					$row.append(srcSelect);

					// Operator select
					let opSelect = '<select class="wlgf-select wlgf-select--sm wlgf-rule-operator">';
					conditionOperators.forEach(function(op) {
						opSelect += '<option value="' + op.value + '"' + (op.value === rule.operator ? ' selected' : '') + '>' + op.label + '</option>';
					});
					opSelect += '</select>';
					$row.append(opSelect);

					// Value input
					const valHiddenClass = (rule.operator === 'is_empty' || rule.operator === 'is_not_empty') ? 'wlgf-d-none' : '';
					const valInput = '<input type="text" class="wlgf-input wlgf-input--sm wlgf-rule-value ' + valHiddenClass + '" value="' + safeEscape(rule.value || '') + '" placeholder="Comparison value...">';
					$row.append(valInput);

					// Delete rule button
					$row.append('<button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-remove-rule-btn" title="Remove Rule"><i class="fa-solid fa-xmark"></i></button>');

					$rulesList.append($row);
				});

				$card.append($rulesList);
				$card.append('<div class="wlgf-mt-2"><button type="button" class="wlgf-btn wlgf-btn--outline wlgf-btn--sm wlgf-add-rule-btn"><i class="fa-solid fa-plus"></i> Add Rule</button></div>');

				$container.append($card);
			});
		}

		$(document).on('click', '#wlgf-add-condition-btn', function(e) {
			e.preventDefault();
			if (!isPro) {
				$('#wlgf-pro-modal-title').text('Unlock Visual Conditional Logic (PRO Feature)');
				$('#wlgf-pro-feature-modal').removeClass('wlgf-d-none').addClass('wlgf-modal-overlay--active');
				return;
			}
			const availableFields = getCanvasFieldList();
			if (!availableFields.length) {
				if (typeof wlgfAlert === 'function') {
					wlgfAlert({
						title: 'Form Canvas Is Empty',
						message: 'Please add at least one field to the form canvas before configuring conditional logic rules.',
						type: 'warning'
					});
				} else {
					alert('Please add fields to the form canvas first before configuring conditional logic.');
				}
				return;
			}

			let targetKey = availableFields[0].name;
			if (formConditions[targetKey]) {
				const unused = availableFields.find(function(f) { return !formConditions[f.name]; });
				if (unused) {
					targetKey = unused.name;
				}
			}

			const sourceField = availableFields.length > 1 ? availableFields[1].name : availableFields[0].name;

			formConditions[targetKey] = {
				action: 'show',
				logic: 'all',
				rules: [
					{
						field: sourceField,
						operator: 'equals',
						value: ''
					}
				]
			};

			renderConditionsUI();
		});

		$(document).on('change', '.wlgf-condition-target', function() {
			const oldTarget = $(this).closest('.wlgf-condition-card').data('target-field');
			const newTarget = $(this).val();
			if (oldTarget && newTarget && oldTarget !== newTarget && formConditions[oldTarget]) {
				formConditions[newTarget] = formConditions[oldTarget];
				delete formConditions[oldTarget];
				$(this).closest('.wlgf-condition-card').data('target-field', newTarget);
			}
		});

		$(document).on('change', '.wlgf-condition-action', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			if (formConditions[targetField]) {
				formConditions[targetField].action = $(this).val();
			}
		});

		$(document).on('change', '.wlgf-condition-logic', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			if (formConditions[targetField]) {
				formConditions[targetField].logic = $(this).val();
			}
		});

		$(document).on('change', '.wlgf-rule-source', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			const ruleIdx = $(this).closest('.wlgf-condition-rule-row').data('rule-index');
			if (formConditions[targetField] && formConditions[targetField].rules && formConditions[targetField].rules[ruleIdx]) {
				formConditions[targetField].rules[ruleIdx].field = $(this).val();
			}
		});

		$(document).on('change', '.wlgf-rule-operator', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			const ruleIdx = $(this).closest('.wlgf-condition-rule-row').data('rule-index');
			const op = $(this).val();
			const $valInput = $(this).siblings('.wlgf-rule-value');

			if (op === 'is_empty' || op === 'is_not_empty') {
				$valInput.hide();
			} else {
				$valInput.show();
			}

			if (formConditions[targetField] && formConditions[targetField].rules && formConditions[targetField].rules[ruleIdx]) {
				formConditions[targetField].rules[ruleIdx].operator = op;
			}
		});

		$(document).on('input change', '.wlgf-rule-value', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			const ruleIdx = $(this).closest('.wlgf-condition-rule-row').data('rule-index');
			if (formConditions[targetField] && formConditions[targetField].rules && formConditions[targetField].rules[ruleIdx]) {
				formConditions[targetField].rules[ruleIdx].value = $(this).val();
			}
		});

		$(document).on('click', '.wlgf-remove-condition-btn', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			delete formConditions[targetField];
			renderConditionsUI();
		});

		$(document).on('click', '.wlgf-add-rule-btn', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			const availableFields = getCanvasFieldList();
			const src = availableFields[0] ? availableFields[0].name : '';

			if (formConditions[targetField]) {
				formConditions[targetField].rules.push({
					field: src,
					operator: 'equals',
					value: ''
				});
				renderConditionsUI();
			}
		});

		$(document).on('click', '.wlgf-remove-rule-btn', function() {
			const targetField = $(this).closest('.wlgf-condition-card').data('target-field');
			const ruleIdx = $(this).closest('.wlgf-condition-rule-row').data('rule-index');

			if (formConditions[targetField] && formConditions[targetField].rules) {
				formConditions[targetField].rules.splice(ruleIdx, 1);
				if (!formConditions[targetField].rules.length) {
					delete formConditions[targetField];
				}
				renderConditionsUI();
			}
		});

		// Live Preview Modal Open & Render
		$(document).on('click', '#wlgf-open-preview-btn', function(e) {
			e.preventDefault();
			if (!formBuilder) {
				return;
			}

			// Flush any in-flight contenteditable or field input edits before extracting data
			$('#build-wrap').find('.fld-label, [contenteditable], input, select, textarea').trigger('blur');

			const rawData = formBuilder.actions.getData();
			let parsedFields = Array.isArray(rawData) ? rawData : [];
			if (!parsedFields.length && typeof rawData === 'string') {
				try {
					parsedFields = JSON.parse(rawData);
				} catch(err) {}
			}
			const formName = $('#wlgf-form-name').val().trim() || 'Form Preview';
			const primaryColor = $('#wlgf-theme-primary').val().trim() || '#2563eb';
			const borderRadius = $('#wlgf-theme-radius').val() || '6px';
			const bgMode = $('#wlgf-theme-bg').val() || 'card';
			const fontFamily = $('#wlgf-theme-font').val() || 'inherit';
			const btnWidth = $('#wlgf-theme-btn-width').val() || 'auto';
			const fieldSize = $('#wlgf-theme-field-size').val() || 'default';
			const mode = $('#wlgf-theme-mode').val() || 'light';
			const inputStyle = $('#wlgf-theme-input-style').val() || 'bordered';
			const maxWidth = $('#wlgf-theme-max-width').val() || '640px';
			const btnShadow = $('#wlgf-theme-btn-shadow').val() || 'default';
			const labelStyle = $('#wlgf-theme-label-style').val() || 'default';

			let html = '<div class="wlgf-container" data-theme-bg="' + bgMode + '" data-theme-mode="' + mode + '" data-theme-field-size="' + fieldSize + '" data-theme-btn-width="' + btnWidth + '" data-theme-input-style="' + inputStyle + '" data-theme-btn-shadow="' + btnShadow + '" data-theme-label-style="' + labelStyle + '" style="--wlgf-primary: ' + primaryColor + '; --wlgf-radius: ' + borderRadius + '; --wlgf-max-width: ' + maxWidth + '; font-family: ' + fontFamily + ';">';
			html += '<div class="wlgf-form-preview-grid" style="display:flex; flex-wrap:wrap; margin-left:-8px; margin-right:-8px;">';

			if (!parsedFields || parsedFields.length === 0) {
				html += '<p class="wlgf-text-muted wlgf-col-12" style="padding: 0 8px;">Canvas is currently empty. Add fields to view live preview.</p>';
			} else {
				parsedFields.forEach(function(field) {
					const reqStar = field.required ? ' <span class="wlgf-text-danger">*</span>' : '';
					const label = field.label ? safeEscape(field.label) : '';
					const placeholder = field.placeholder ? safeEscape(field.placeholder) : '';
					const colNum = field.column_width || '12';
					const colClass = 'wlgf-col-' + colNum;
					const tooltipHtml = (field.description && field.description.trim() !== '')
						? ' <span class="wlgf-tooltip-icon" data-wlgf-tooltip="' + safeEscape(field.description) + '" data-bs-toggle="tooltip" title="' + safeEscape(field.description) + '">?</span>'
						: '';

					if (field.type === 'step-break') {
						html += '<div class="wlgf-preview-field wlgf-step-break wlgf-col-12" style="box-sizing:border-box; padding:0 8px;"><i class="fa-solid fa-layer-group"></i> ── ' + (label || 'Step Break (Page Break)') + ' ──</div>';
					} else if (field.type === 'divider') {
						html += '<div class="wlgf-preview-field wlgf-preview-divider ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><hr class="wlgf-form-divider"><span class="wlgf-text-muted wlgf-text-xs">' + (label && label !== 'Section Divider' ? label : '── Section Divider ──') + '</span></div>';
					} else if (field.type === 'terms') {
						html += '<div class="wlgf-preview-field wlgf-preview-terms ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label class="wlgf-checkbox-label"><input type="checkbox" checked disabled> <span>' + (label || 'I agree to the Terms of Service and Privacy Policy') + reqStar + tooltipHtml + '</span></label></div>';
					} else if (field.type === 'header') {
						const validTags = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
						const tag = (field.subtype && validTags.indexOf(field.subtype.toLowerCase()) !== -1) ? field.subtype.toLowerCase() : 'h1';
						const cls = field.className ? safeEscape(field.className) : '';
						html += '<div class="wlgf-preview-field wlgf-header-wrap mb-3 ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><' + tag + ' class="wlgf-preview-heading ' + cls + '">' + label + '</' + tag + '></div>';
					} else if (field.type === 'paragraph') {
						const validPTags = ['p', 'div', 'span', 'section'];
						const ptag = (field.subtype && validPTags.indexOf(field.subtype.toLowerCase()) !== -1) ? field.subtype.toLowerCase() : 'p';
						const pcls = field.className ? safeEscape(field.className) : '';
						html += '<div class="wlgf-preview-field wlgf-paragraph-wrap mb-3 ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><' + ptag + ' class="wlgf-preview-desc ' + pcls + '">' + label + '</' + ptag + '></div>';
					} else if (field.type === 'text' || field.type === 'email' || field.type === 'number' || field.type === 'date' || field.type === 'tel' || (field.type === 'text' && field.subtype)) {
						const inputType = field.subtype || field.type;
						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><input type="' + inputType + '" placeholder="' + placeholder + '"></div>';
					} else if (field.type === 'textarea') {
						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><textarea rows="3" placeholder="' + placeholder + '"></textarea></div>';
					} else if (field.type === 'select') {
						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><select>';
						if (field.values && field.values.length > 0) {
							field.values.forEach(function(opt) {
								html += '<option value="' + safeEscape(opt.value) + '">' + safeEscape(opt.label) + '</option>';
							});
						}
						html += '</select></div>';
					} else if (field.type === 'radio-group' || field.type === 'checkbox-group') {
						const inputType = field.type === 'radio-group' ? 'radio' : 'checkbox';
						
						// Check if this is a payment method field while payments are disabled in Form Settings
						const isPaymentField = (field.name && (field.name.toLowerCase().includes('payment_method') || field.name.toLowerCase().includes('payment-method')));
						let hasGatewayVals = false;
						if (field.values && field.values.length > 0) {
							field.values.forEach(function(opt) {
								const s = ((opt.value || '') + ' ' + (opt.label || '')).toLowerCase();
								if (s.includes('stripe') || s.includes('paypal') || s.includes('credit card')) {
									hasGatewayVals = true;
								}
							});
						}
						let paymentNoticeHtml = '';
						if ((isPaymentField || hasGatewayVals) && !$('#wlgf-payment-enabled').is(':checked')) {
							paymentNoticeHtml = '<div class="wlgf-payment-preview-notice"><i class="fa-solid fa-triangle-exclamation"></i> <span><strong>Payment is disabled:</strong> "Enable Payment" is unchecked in Form Settings. This field will be hidden from public visitors.</span></div>';
						}

						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;">' + paymentNoticeHtml + '<label>' + label + reqStar + tooltipHtml + '</label><div class="wlgf-mt-1">';
						if (field.values && field.values.length > 0) {
							field.values.forEach(function(opt) {
								const checkedAttr = (opt.selected || opt.checked) ? ' checked' : '';
								html += '<label class="wlgf-radio-label"><input type="' + inputType + '" name="preview_' + safeEscape(field.name) + '"' + checkedAttr + '> <span class="wlgf-radio-text">' + safeEscape(opt.label) + '</span></label>';
							});
						}
						html += '</div></div>';
					} else if (field.type === 'file') {
						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><input type="file"></div>';
					} else if (field.type === 'star-rating') {
						html += '<div class="wlgf-preview-field wlgf-preview-stars ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><div class="wlgf-stars-display"><i class="fa-solid fa-star wlgf-text-warning"></i> <i class="fa-solid fa-star wlgf-text-warning"></i> <i class="fa-solid fa-star wlgf-text-warning"></i> <i class="fa-solid fa-star wlgf-text-warning"></i> <i class="fa-solid fa-star wlgf-text-warning"></i></div></div>';
					} else if (field.type === 'nps-matrix') {
						let chips = '';
						for (let i = 0; i <= 10; i++) {
							chips += '<span class="wlgf-nps-chip">' + i + '</span> ';
						}
						html += '<div class="wlgf-preview-field wlgf-preview-nps ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><div class="wlgf-nps-chips wlgf-mt-1">' + chips + '</div><div class="wlgf-text-muted wlgf-text-xs wlgf-mt-1">(0 = Not Likely, 10 = Extremely Likely)</div></div>';
					} else if (field.type === 'calculation') {
						const prefix = field.prefix !== undefined ? field.prefix : '$';
						const postfix = field.postfix || '';
						const formula = field.formula || '';
						html += '<div class="wlgf-preview-field wlgf-mb-3 ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + tooltipHtml + '</label><div class="wlgf-badge wlgf-badge--primary"><i class="fa-solid fa-calculator"></i> <span>' + safeEscape(prefix + '0.00' + postfix) + '</span> <span class="wlgf-text-xs wlgf-text-muted">(' + safeEscape(formula || 'Formula') + ')</span></div></div>';
					} else if (field.type === 'hidden') {
						html += '<div class="wlgf-preview-field wlgf-preview-hidden wlgf-text-muted wlgf-text-xs wlgf-col-12" style="box-sizing:border-box; padding:0 8px;"><i class="fa-solid fa-eye-slash"></i> Hidden Input: <code>' + safeEscape(field.name || 'hidden') + '</code>' + (field.value ? (' = "' + safeEscape(field.value) + '"') : '') + '</div>';
					} else if (field.type === 'button') {
						const btnLabel = field.label ? safeEscape(field.label) : 'Submit';
						
						// Render simulated newsletter opt-in checkbox directly above submit button if enabled
						if ($('#wlgf-newsletter-enabled').is(':checked') && $('#wlgf-newsletter-mode').val() === 'explicit') {
							const nlLabel = $('#wlgf-newsletter-label').val().trim() || 'Subscribe to our newsletter for exclusive updates and offers';
							const nlChecked = $('#wlgf-newsletter-default-checked').is(':checked') ? 'checked' : '';
							html += '<div class="wlgf-preview-field wlgf-mb-3 wlgf-col-12" style="box-sizing:border-box; padding:0 8px;"><label class="wlgf-checkbox-label"><input type="checkbox" ' + nlChecked + '> <span>' + safeEscape(nlLabel) + '</span></label></div>';
						}

						// Render simulated payment button if payment enabled
						if ($('#wlgf-payment-enabled').is(':checked')) {
							const itemDesc = $('#wlgf-payment-item-name').val().trim() || 'Online Checkout';
							const payType = $('#wlgf-payment-type').val();
							const payAmt = payType === 'fixed' ? ('$' + ($('#wlgf-payment-amount').val() || '0.00')) : 'Calculated Total';
							html += '<div class="wlgf-preview-field wlgf-mb-3 wlgf-payment-preview-card wlgf-col-12" style="box-sizing:border-box; padding:0 8px;"><div class="wlgf-payment-preview-header"><i class="fa-solid fa-credit-card"></i> ' + safeEscape(itemDesc + ' (' + payAmt + ')') + '</div><div class="wlgf-payment-preview-btns"><button type="button" class="btn wlgf-btn-stripe"><i class="fa-brands fa-stripe"></i> Pay with Card</button><button type="button" class="btn wlgf-btn-paypal"><i class="fa-brands fa-paypal"></i> PayPal</button></div></div>';
						}

						const btnCls = field.className || '';
						let btnStyleClass = 'btn-primary';
						if (btnCls.indexOf('btn-danger') !== -1) {
							btnStyleClass = 'btn-danger';
						} else if (btnCls.indexOf('btn-success') !== -1) {
							btnStyleClass = 'btn-success';
						} else if (btnCls.indexOf('btn-warning') !== -1) {
							btnStyleClass = 'btn-warning';
						} else if (btnCls.indexOf('btn-info') !== -1) {
							btnStyleClass = 'btn-info';
						} else if (btnCls.indexOf('btn-default') !== -1) {
							btnStyleClass = 'btn-default';
						}

						html += '<div class="wlgf-preview-field wlgf-button-wrap wlgf-col-12 mb-3 ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><button type="button" class="wlgf-btn wlgf-btn--primary ' + btnStyleClass + '">' + btnLabel + '</button></div>';
					} else {
						// Generic fallback for any other or custom input type
						const inputType = field.subtype || field.type || 'text';
						html += '<div class="wlgf-preview-field ' + colClass + '" style="box-sizing:border-box; padding:0 8px;"><label>' + label + reqStar + tooltipHtml + '</label><input type="' + inputType + '" placeholder="' + placeholder + '"></div>';
					}
				});
			}

			html += '</div></div>';
			$('#wlgf-preview-render-mount').html(html);
			const $previewModal = $('#wlgf-preview-modal');
			const $previewDialog = $previewModal.find('.wlgf-modal--preview');
			const $previewMount = $('#wlgf-preview-render-mount');
			$previewModal.find('.wlgf-preview-device-switcher .wlgf-device-btn').removeClass('active');
			$previewModal.find('.wlgf-preview-device-switcher .wlgf-device-btn[data-device="desktop"]').addClass('active');
			$previewDialog.removeClass('wlgf-preview-mode--tablet wlgf-preview-mode--mobile').addClass('wlgf-preview-mode--desktop');
			$previewMount.removeClass('wlgf-preview--tablet wlgf-preview--mobile').addClass('wlgf-preview--desktop').css({ maxWidth: '100%', margin: '0 auto' });
			$previewModal.addClass('wlgf-modal-overlay--active').removeClass('wlgf-d-none');
		});

		// Device Viewport Switcher in Live Preview (Desktop, Tablet, Mobile)
		$(document).on('click', '.wlgf-preview-device-switcher .wlgf-device-btn', function(e) {
			e.preventDefault();
			$('.wlgf-preview-device-switcher .wlgf-device-btn').removeClass('active');
			$(this).addClass('active');
			const dev = $(this).data('device');
			const $dialog = $('#wlgf-preview-modal .wlgf-modal--preview');
			const $mount = $('#wlgf-preview-render-mount');

			$dialog.removeClass('wlgf-preview-mode--desktop wlgf-preview-mode--tablet wlgf-preview-mode--mobile');
			$mount.removeClass('wlgf-preview--desktop wlgf-preview--tablet wlgf-preview--mobile');

			if (dev === 'tablet') {
				$dialog.addClass('wlgf-preview-mode--tablet');
				$mount.addClass('wlgf-preview--tablet').css({ maxWidth: '768px', margin: '0 auto', transition: 'max-width 0.3s ease' });
			} else if (dev === 'mobile') {
				$dialog.addClass('wlgf-preview-mode--mobile');
				$mount.addClass('wlgf-preview--mobile').css({ maxWidth: '375px', margin: '0 auto', transition: 'max-width 0.3s ease' });
			} else {
				$dialog.addClass('wlgf-preview-mode--desktop');
				$mount.addClass('wlgf-preview--desktop').css({ maxWidth: '100%', margin: '0 auto', transition: 'max-width 0.3s ease' });
			}
		});

		$(document).on('click', '#wlgf-close-preview-btn, #wlgf-close-preview-footer-btn, #wlgf-preview-modal', function(e) {
			if (e.target === this || $(this).is('#wlgf-close-preview-btn, #wlgf-close-preview-footer-btn')) {
				$('#wlgf-preview-modal').removeClass('wlgf-modal-overlay--active').addClass('wlgf-d-none');
			}
		});

		// Newsletter Drawer Toggle Handlers (PRO-008)
		$('#wlgf-newsletter-enabled').on('change', function() {
			if (this.checked) {
				$('#wlgf-newsletter-settings-drawer').removeClass('d-none');
			} else {
				$('#wlgf-newsletter-settings-drawer').addClass('d-none');
			}
		});

		$('#wlgf-newsletter-mode').on('change', function() {
			if ($(this).val() === 'automatic') {
				$('#wlgf-gdpr-notice').removeClass('d-none');
				$('#wlgf-explicit-checkbox-options').addClass('d-none');
			} else {
				$('#wlgf-gdpr-notice').addClass('d-none');
				$('#wlgf-explicit-checkbox-options').removeClass('d-none');
			}
		});

		// Payment Drawer Handlers (PRO-016)
		function updatePaymentCalcDropdown() {
			const $dropdown = $('#wlgf-payment-calc-field');
			const currentVal = $dropdown.data('selected') || $dropdown.val();
			$dropdown.empty().append('<option value="">-- Select Calculation Field --</option>');

			if (formBuilder && formBuilder.actions) {
				const fields = formBuilder.actions.getData();
				if (Array.isArray(fields)) {
					fields.forEach(function(f) {
						if (f.type === 'calculation' || f.type === 'number') {
							const name = f.name || f.label;
							const label = f.label ? (f.label + ' (' + (f.name || f.type) + ')') : (f.name || f.type);
							const isSel = (name === currentVal) ? 'selected' : '';
							$dropdown.append('<option value="' + safeEscape(name) + '" ' + isSel + '>' + safeEscape(label) + '</option>');
						}
					});
				}
			}
		}

		$('#wlgf-payment-enabled').on('change', function() {
			if (this.checked) {
				$('#wlgf-payment-settings-drawer').removeClass('d-none');
				updatePaymentCalcDropdown();
			} else {
				$('#wlgf-payment-settings-drawer').addClass('d-none');
			}
		});

		$('#wlgf-payment-type').on('change', function() {
			if ($(this).val() === 'calculation') {
				$('#wlgf-payment-calc-wrap').removeClass('d-none');
				$('#wlgf-payment-fixed-wrap').addClass('d-none');
				updatePaymentCalcDropdown();
			} else {
				$('#wlgf-payment-calc-wrap').addClass('d-none');
				$('#wlgf-payment-fixed-wrap').removeClass('d-none');
			}
		});

		$('#wlgf-payment-accordion .wlgf-collapsible__header').on('click', function() {
			updatePaymentCalcDropdown();
		});

		if (FormGenerator.PaymentEnabled) {
			updatePaymentCalcDropdown();
		}

		// Helper function for save/update feedback notifications
		function showSaveFeedback(type, message) {
			const icon = (type === 'success') ? 'fa-circle-check' : 'fa-circle-exclamation';
			const alertClass = (type === 'success') ? 'wlgf-alert--success' : 'wlgf-alert--danger';
			const html = '<div class="wlgf-alert ' + alertClass + ' wlgf-mb-3">' +
				'<i class="fa-solid ' + icon + '"></i>' +
				'<span>' + message + '</span>' +
				'</div>';

			let $global = $('#wlgf-global-feedback');
			if (!$global.length) {
				$('.wlgf-card:first').before('<div id="wlgf-global-feedback" class="wlgf-mb-3"></div>');
				$global = $('#wlgf-global-feedback');
			}
			$global.html(html);

			if (type === 'danger') {
				$('html, body').animate({ scrollTop: Math.max(0, $global.offset().top - 80) }, 300);
			}
		}

		// Save or Modify Form Handler (Synchronized for Header and Bottom Buttons)
		$('body').on('click', '.wlgf-action-button, #wlgf-header-save-btn', function(e) {
			e.preventDefault();

			// Resolve form ID: Check hidden input, FormGenerator global, or URL parameters
			let wlgf_form_id = $('input#wlgf-modify-form-id').val();
			if (!wlgf_form_id && FormGenerator.FormIdSet === 'set' && FormGenerator.FormId) {
				wlgf_form_id = FormGenerator.FormId;
			}
			if (!wlgf_form_id) {
				const urlParams = new URLSearchParams(window.location.search);
				if (urlParams.get('form-id')) {
					wlgf_form_id = urlParams.get('form-id');
				}
			}

			const isModify = (wlgf_form_id && wlgf_form_id !== '0' && wlgf_form_id !== 0 && wlgf_form_id !== '');
			const wlgf_form_nonce = isModify ? FormGenerator.ModifyNonce : FormGenerator.SaveNonce;

			// Validate Form Name
			const wlgf_form_name = $('#wlgf-form-name').val().trim();
			if (!wlgf_form_name) {
				$('#wlgf-form-name').focus().addClass('wlgf-input--error');
				showSaveFeedback('danger', 'Please enter a Form Name before saving.');
				setTimeout(function() {
					$('#wlgf-form-name').removeClass('wlgf-input--error');
				}, 3000);
				return false;
			}

			// Pre-save synchronization: Ensure any option whose value is empty gets populated from its label
			$('.frmb .field-options .sortable-options > li').each(function() {
				const $lbl = $(this).find('input.option-label');
				const $val = $(this).find('input.option-value');
				if ($lbl.length && $val.length) {
					const l = ($lbl.val() || '').trim();
					const v = ($val.val() || '').trim();
					if (!v && l) {
						$val.val(l).trigger('change');
					}
				}
			});

			// Safely access FormBuilder actions across jQuery data and global instance
			const fbInstance = (formBuilder && formBuilder.actions) ? formBuilder : $(fbEditor).data('formBuilder');
			let rawSaveData = [];
			if (fbInstance && fbInstance.actions && typeof fbInstance.actions.getData === 'function') {
				rawSaveData = fbInstance.actions.getData();
			} else if (formBuilder && typeof formBuilder.actions !== 'undefined' && typeof formBuilder.actions.getData === 'function') {
				rawSaveData = formBuilder.actions.getData();
			}

			let wlgf_parsed_array = Array.isArray(rawSaveData) ? rawSaveData : [];
			if (!wlgf_parsed_array.length && typeof rawSaveData === 'string') {
				try {
					wlgf_parsed_array = JSON.parse(rawSaveData);
				} catch(err) {}
			}

			// Auto-strip any stray HTML tags or clipboard span styles from field and option labels before saving
			if (Array.isArray(wlgf_parsed_array)) {
				wlgf_parsed_array.forEach(function(f) {
					if (f && f.label && f.type !== 'paragraph' && f.type !== 'header') {
						const tmp = document.createElement('div');
						tmp.innerHTML = f.label;
						f.label = (tmp.textContent || tmp.innerText || f.label).trim();
					}
					if (f && Array.isArray(f.values)) {
						f.values.forEach(function(v) {
							if (v && v.label) {
								const tmp = document.createElement('div');
								tmp.innerHTML = v.label;
								v.label = (tmp.textContent || tmp.innerText || v.label).trim();
							}
						});
					}
				});
			}

			if (!wlgf_parsed_array || wlgf_parsed_array.length === 0) {
				const $btns = $('.wlgf-action-button, #wlgf-header-save-btn');
				$btns.addClass('wlgf-btn--shake');
				showSaveFeedback('danger', 'Please drag and add at least one field to the form canvas before saving.');
				setTimeout(function() {
					$btns.removeClass('wlgf-btn--shake');
				}, 500);
				return false;
			}

			const getSafeVal = function(selector, fallback) {
				const el = $(selector);
				if (!el.length || el.val() === undefined || el.val() === null) {
					return (fallback !== undefined) ? fallback : '';
				}
				return String(el.val()).trim();
			};

			const confirmationType = getSafeVal('#wlgf-confirmation-type', 'message') || 'message';
			const redirectUrl = getSafeVal('#wlgf-redirect-url', '');
			const customMessage = getSafeVal('#wlgf-custom-message', '');
			const autoReplyEnabled = $('#wlgf-auto-reply-enabled').is(':checked') ? 1 : 0;
			const defaultSubj = (typeof FormGenerator !== 'undefined' && FormGenerator.DefaultReplySubj) ? FormGenerator.DefaultReplySubj : 'Thank you for your inquiry! We received your message.';
			const defaultBody = (typeof FormGenerator !== 'undefined' && FormGenerator.DefaultReplyBody) ? FormGenerator.DefaultReplyBody : '';
			const autoReplySubject = getSafeVal('#wlgf-auto-reply-subject', '') || (autoReplyEnabled ? defaultSubj : '');
			const autoReplyBody = getSafeVal('#wlgf-auto-reply-body', '') || (autoReplyEnabled ? defaultBody : '');
			const themePrimary = getSafeVal('#wlgf-theme-primary', '#2563eb') || '#2563eb';
			const themeRadius = getSafeVal('#wlgf-theme-radius', '6px') || '6px';
			const themeBg = getSafeVal('#wlgf-theme-bg', 'card') || 'card';
			const themeFont = getSafeVal('#wlgf-theme-font', 'inherit') || 'inherit';
			const webhookUrl = getSafeVal('#wlgf-webhook-url', '');
			const webhookAuth = getSafeVal('#wlgf-webhook-auth', '');
			const scheduleStart = getSafeVal('#wlgf-schedule-start', '');
			const scheduleEnd = getSafeVal('#wlgf-schedule-end', '');
			const maxSubmissions = getSafeVal('#wlgf-max-submissions', '0') || 0;
			const closedMessage = getSafeVal('#wlgf-closed-message', '');
			const newsletterEnabled = $('#wlgf-newsletter-enabled').is(':checked') ? 1 : 0;
			const newsletterMode = getSafeVal('#wlgf-newsletter-mode', 'explicit') || 'explicit';
			const newsletterLabel = getSafeVal('#wlgf-newsletter-label', '');
			const newsletterDefaultChecked = $('#wlgf-newsletter-default-checked').is(':checked') ? 1 : 0;
			const newsletterTag = getSafeVal('#wlgf-newsletter-tag', '');

			// Phase 2: Multi-Step & Conditions
			const wizardStyle = getSafeVal('#wlgf-wizard-style', 'circles') || 'circles';
			const wizardStep1Title = getSafeVal('#wlgf-wizard-step1-title', '');
			const wizardPrevText = getSafeVal('#wlgf-wizard-prev-text', 'Previous') || 'Previous';
			const wizardNextText = getSafeVal('#wlgf-wizard-next-text', 'Next Step') || 'Next Step';

			// Phase 4 / PRO-016: Payments
			const paymentEnabled = $('#wlgf-payment-enabled').is(':checked') ? 1 : 0;
			const paymentType = getSafeVal('#wlgf-payment-type', 'fixed') || 'fixed';
			const paymentAmount = getSafeVal('#wlgf-payment-amount', '0.00') || '0.00';
			const paymentCalcField = getSafeVal('#wlgf-payment-calc-field', '');
			const paymentItemName = getSafeVal('#wlgf-payment-item-name', '');

			// In-flight status display for all save buttons
			const $allSaveBtns = $('.wlgf-action-button, #wlgf-header-save-btn');
			$allSaveBtns.prop('disabled', true).html(
				'<i class="fa-solid fa-spinner fa-spin"></i> ' + (isModify ? 'Updating Form...' : 'Saving Form...')
			);

			$.ajax({
				type: 'POST',
				url: FormGenerator.ajaxUrl,
				data: {
					action: 'wlgf_save_form',
					id: isModify ? wlgf_form_id : 0,
					name: wlgf_form_name,
					form: JSON.stringify(wlgf_parsed_array),
					confirmation_type: confirmationType,
					redirect_url: redirectUrl,
					custom_message: customMessage,
					auto_reply_enabled: autoReplyEnabled,
					auto_reply_subject: autoReplySubject,
					auto_reply_body: autoReplyBody,
					theme_primary: themePrimary,
					theme_radius: themeRadius,
					theme_bg: themeBg,
					theme_font: themeFont,
					theme_btn_width: $('#wlgf-theme-btn-width').val() || 'auto',
					theme_field_size: $('#wlgf-theme-field-size').val() || 'default',
					theme_mode: $('#wlgf-theme-mode').val() || 'light',
					theme_input_style: $('#wlgf-theme-input-style').val() || 'bordered',
					theme_max_width: $('#wlgf-theme-max-width').val() || '640px',
					theme_btn_shadow: $('#wlgf-theme-btn-shadow').val() || 'default',
					theme_label_style: $('#wlgf-theme-label-style').val() || 'default',
					webhook_url: webhookUrl,
					webhook_auth: webhookAuth,
					schedule_start: scheduleStart,
					schedule_end: scheduleEnd,
					max_submissions: maxSubmissions,
					closed_message: closedMessage,
					newsletter_enabled: newsletterEnabled,
					newsletter_mode: newsletterMode,
					newsletter_label: newsletterLabel,
					newsletter_default_checked: newsletterDefaultChecked,
					newsletter_tag: newsletterTag,
					wizard_style: wizardStyle,
					wizard_step_1_title: wizardStep1Title,
					wizard_prev_text: wizardPrevText,
					wizard_next_text: wizardNextText,
					conditions: formConditions,
					payment_enabled: paymentEnabled,
					payment_type: paymentType,
					payment_amount: paymentAmount,
					payment_calc_field: paymentCalcField,
					payment_item_name: paymentItemName,
					display_mode: $('#wlgf-display-mode').val() || 'inline',
					display_position: $('#wlgf-display-position').val() || 'right',
					trigger_type: $('#wlgf-trigger-type').val() || 'time_delay',
					trigger_delay: $('#wlgf-trigger-delay').val() || 5,
					trigger_scroll: $('#wlgf-trigger-scroll').val() || 50,
					trigger_frequency: 7,
					capture_abandoned: $('#wlgf-capture-abandoned').is(':checked') ? 1 : 0,
					nonce: wlgf_form_nonce
				},
				success: function(result) {
					if (result) {
						$('#wlgf-button-div').html(result);
						const savedFormId = $('#wlgf-modify-form-id').val() || wlgf_form_id;
						if (savedFormId) {
							if (window.history && window.history.replaceState) {
								const newUrl = new URL(window.location.href);
								newUrl.searchParams.set('form-id', savedFormId);
								window.history.replaceState(null, '', newUrl.toString());
							}
							FormGenerator.FormIdSet = 'set';
							FormGenerator.FormId = savedFormId;

							const currentName = $('#wlgf-form-name').val().trim();
							if (currentName) {
								$('.wlgf-header__title').text('Edit Form: ' + currentName);
							}
							$('.wlgf-header__title-row .wlgf-badge')
								.removeClass('wlgf-badge--success')
								.addClass('wlgf-badge--primary')
								.text('#' + savedFormId);
						}

						// Prominent success feedback notice
						showSaveFeedback('success', isModify ? 'Form updated successfully!' : 'Form created and saved successfully!');

						// Update save buttons to Success status with checkmark
						const $btns = $('.wlgf-action-button, #wlgf-header-save-btn');
						$btns
							.prop('disabled', false)
							.removeClass('wlgf-btn--primary wlgf-btn--danger')
							.addClass('wlgf-btn--success')
							.html('<i class="fa-solid fa-circle-check"></i> ' + (isModify ? 'Updated!' : 'Saved!'));

						// Revert to stable Update Form button after 2.5 seconds
						setTimeout(function() {
							$btns
								.removeClass('wlgf-btn--success')
								.addClass('wlgf-btn--primary')
								.html('<i class="fa-solid fa-file-pen"></i> Update Form');
						}, 2500);
					}
				},
				error: function() {
					showSaveFeedback('danger', 'An error occurred while saving the form. Please check your network and try again.');
					const $btns = $('.wlgf-action-button, #wlgf-header-save-btn');
					$btns
						.prop('disabled', false)
						.removeClass('wlgf-btn--primary wlgf-btn--success')
						.addClass('wlgf-btn--danger')
						.html('<i class="fa-solid fa-triangle-exclamation"></i> Save Failed');

					setTimeout(function() {
						$btns
							.removeClass('wlgf-btn--danger')
							.addClass('wlgf-btn--primary')
							.html(isModify ? '<i class="fa-solid fa-file-pen"></i> Update Form' : '<i class="fa-solid fa-floppy-disk"></i> Save Form');
					}, 3000);
				}
			});
		});

		// Sticky Header Scroll State Toggle
		const $stickyHeader = $('.wlgf-header--sticky');
		if ($stickyHeader.length) {
			const handleStickyScroll = function() {
				const isStuck = window.scrollY > 16;
				$stickyHeader.toggleClass('wlgf-header--is-stuck', isStuck);
			};
			window.addEventListener('scroll', handleStickyScroll, { passive: true });
			handleStickyScroll();
		}
	});

})(jQuery);