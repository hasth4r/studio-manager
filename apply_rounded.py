import os
import re

views_dir = r"c:\xampp\htdocs\eso8manager_v0.0.1\app\Views"

def process_file(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    new_content = content
    # Replace standard button rounded classes with rounded-full
    # Examples: rounded font-medium, rounded px-4, rounded-lg font-semibold
    # Note: we need to be careful not to replace rounded classes on input fields or cards.
    # We only want to replace it on buttons. We can regex replace inside <button class="..."> or <a> tags that look like buttons.
    
    # Simple regex to find <button ... class="..."> and replace rounded with rounded-full
    def replace_rounded(match):
        inner = match.group(0)
        # Only modify if it looks like a button with our gradient or bg-ytCard etc
        if 'bg-' in inner or 'gradient' in inner:
            inner = re.sub(r'\brounded\b', 'rounded-full', inner)
            inner = re.sub(r'\brounded-lg\b', 'rounded-full', inner)
            inner = re.sub(r'\brounded-md\b', 'rounded-full', inner)
        return inner

    new_content = re.sub(r'<button[^>]*class="[^"]*"[^>]*>', replace_rounded, new_content)
    new_content = re.sub(r'<a[^>]*class="[^"]*bg-ytBlue[^"]*"[^>]*>', replace_rounded, new_content)
    new_content = re.sub(r'<a[^>]*class="[^"]*gradient[^"]*"[^>]*>', replace_rounded, new_content)
    
    if new_content != content:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(new_content)
        print(f"Updated rounded corners in {filepath}")

for root, dirs, files in os.walk(views_dir):
    for file in files:
        if file.endswith('.php'):
            process_file(os.path.join(root, file))
