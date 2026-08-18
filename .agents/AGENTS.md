# Deployment & Live Environment Rules

- **Live Server Deployment**: We are actively fixing and testing directly against the LIVE site. Every single code change, fix, or refactor MUST be immediately committed and pushed (`git add -A; git commit -m "..."; git push origin main`) so changes reflect on the live environment instantly. Never leave changes unpushed.

# UI Guidelines

## Inline Editing
Always use inline dropdowns (`<select>`) and input fields that automatically save (`onchange="this.form.submit()"`) directly in table views or list pages instead of creating separate "Edit" modals or sending users to a details page for basic fields (like status, priority, fps, etc). 
This allows users to edit values immediately from the list itself.
