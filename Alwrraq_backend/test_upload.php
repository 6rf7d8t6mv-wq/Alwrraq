<?php
// Test PDF creation
$pdfContent = "%PDF-1.4\n1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 612 792] /Contents 4 0 R /Resources << /Font << /F1 5 0 R >> >> >>\nendobj\n4 0 obj\n<< /Length 44 >>\nstream\nBT\n/F1 12 Tf\n100 700 Td\n(Test PDF) Tj\nET\nendstream\nendobj\n5 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\nxref\n0 6\n0000000000 65535 f\n0000000009 00000 n\n0000000058 00000 n\n0000000115 00000 n\n0000000234 00000 n\n0000000328 00000 n\ntrailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n417\n%%EOF";

file_put_contents('/tmp/test.pdf', $pdfContent);
echo "Test PDF created at /tmp/test.pdf\n";

// Create test DOCX (it's a ZIP file)
$docxContent = base64_decode('UEsDBBQABgAIAAAAIQCRvTbbAwEAAAwFAAARAAAAd29yZC9kb2N1bWVudC54bWy0VEGPmzAQ/QXLT5hALgEibSU0Ksq2C0cKNS5nwKOtMbYDpg/W/+4bZpoKLT1sHzjyeO69N3527QeAo5xUmSRAKCKwxNhzBQFdTFr0jmMXDlNNTWqCDXmOPe0kZRqkVEWKBCX0mRQzSVu95m2cXqrI7LGIQQQqCCzCEhKVTnJe/RczRJVJvAJXhUJp46ypN0YLKe1fQSdQWN8ht5eQpUJJGD9BFWlyKaLBYyCLpbsX45BG6QIVf8hv5uMoHs5E0c5CwIm2SJCFhOVUCF/Ll1PzwKqKf3s8v8R/4PiC+4e3kgEKqDEqwzZWs/E8tF+m89YH2x6Wq6YKKaLpigpoumiime6mK2XWdB0qYrpkopt2eUdNF0yYMGHChAkTpmIqpmKqpqqmaqqmKgAyZsxYb8xUXsxUUXkxUz0xU33lxUwNRVQBsKleQvbQN9R9u3MnSj4qKJ9F1FQS+W3LAzrS1tRBSmRdgf9d8H8AAAD//wMAUEsDBBQABgAIAAAAIQCGrbQDkQAAAI8AAAARAAAAd29yZC9zdHlsZXMueG1shM/BDsMwDIbhV7mOtGOpxFipCEJpNytTr2E6QiS1s4gkMhynTXj6jKGpEKlTnPj//v+/fX+dngAOpVRVYMAnYEAFqkRNEW0LD2TBnfGlwMbeFHSI7wXa/Zy9eUF0tnb0anCNMQKJCEixfXqvkWzQh5VbPemVnuWa7bPPrLOplJJC+oDyBOtY8c2Y3/8fHmIz3m7Qq2t7OD/AAAA//8DAFBLAQItAxQABgAIAAAAIQCRvTbbAwEAAAwFAAARAAAAAAAAAAAApIEAAAAAd29yZC9kb2N1bWVudC54bWxQSwECLQMUAAYACAAAACEAhq20A5EAAACPAAAAEQAAAAAAAAAAIKSBjAAAAHdvcmQvc3R5bGVzLnhtbFBLBQYAAAAAAgACANAAAADKAAAAACQA');
file_put_contents('/tmp/test.docx', $docxContent);
echo "Test DOCX created at /tmp/test.docx\n";
?>
