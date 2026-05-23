import os

def search_files(directory, query):
    matches = []
    for root, dirs, files in os.walk(directory):
        if 'backups' in root or 'assets' in root or '.git' in root or 'scratch' in root:
            continue
        for file in files:
            if file.endswith('.php') or file.endswith('.js'):
                path = os.path.join(root, file)
                try:
                    with open(path, 'r', encoding='utf-8') as f:
                        lines = f.readlines()
                    for idx, line in enumerate(lines):
                        if query.lower() in line.lower():
                            matches.append((path, idx + 1, line.strip(), lines[max(0, idx-3):idx+4]))
                except Exception as e:
                    pass
    return matches

directory = r"D:\xampp\htdocs\GadgetZone"
query = "subcat"
results = search_files(directory, query)

with open(r"D:\xampp\htdocs\GadgetZone\scratch\search_results.txt", "w", encoding="utf-8") as out:
    out.write(f"Found {len(results)} matches for '{query}':\n")
    for path, line_no, line, context in results:
        out.write("=" * 80 + "\n")
        out.write(f"File: {path} (Line {line_no})\n")
        out.write(f"Match: {line}\n")
        out.write("-" * 40 + "\n")
        out.write("Context:\n")
        for c_line in context:
            out.write(c_line)
        out.write("=" * 80 + "\n\n")

print(f"Done. Wrote {len(results)} matches to scratch/search_results.txt")
