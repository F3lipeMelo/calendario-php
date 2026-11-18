from pathlib import Path
from xml.sax.saxutils import escape
from PIL import Image as PILImage
from docx import Document
from docx.text.paragraph import Paragraph as DocxParagraph
from docx.table import Table as DocxTable
from reportlab.pdfbase import pdfmetrics
from reportlab.pdfbase.ttfonts import TTFont
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.lib.enums import TA_LEFT
from reportlab.platypus import SimpleDocTemplate, Paragraph, Table, TableStyle, Spacer, PageBreak, Image, KeepTogether
from reportlab.lib.units import inch

ROOT = Path(__file__).resolve().parents[1]
SOURCE = ROOT / "docs" / "documentacao-hoshi-calendar.docx"
OUTPUT = ROOT / "docs" / "documentacao-hoshi-calendar.pdf"
pdfmetrics.registerFont(TTFont("Arial", "C:/Windows/Fonts/arial.ttf"))
pdfmetrics.registerFont(TTFont("ArialBold", "C:/Windows/Fonts/arialbd.ttf"))

styles = getSampleStyleSheet()
styles.add(ParagraphStyle(name="BodyCustom", fontName="Arial", fontSize=10.5, leading=15, spaceAfter=7, textColor=colors.HexColor("#1D2635")))
styles.add(ParagraphStyle(name="TitleCustom", fontName="ArialBold", fontSize=23, leading=28, spaceAfter=18, textColor=colors.black))
styles.add(ParagraphStyle(name="Head1Custom", fontName="ArialBold", fontSize=15, leading=19, spaceBefore=15, spaceAfter=8, textColor=colors.black))
styles.add(ParagraphStyle(name="Head2Custom", fontName="ArialBold", fontSize=12, leading=16, spaceBefore=11, spaceAfter=5, textColor=colors.black))
styles.add(ParagraphStyle(name="CellCustom", fontName="Arial", fontSize=8.5, leading=11, textColor=colors.HexColor("#1D2635")))
styles.add(ParagraphStyle(name="CellHeadCustom", fontName="ArialBold", fontSize=8.5, leading=11, textColor=colors.white))

def diagram(path, width=6.8*inch):
    with PILImage.open(path) as picture:
        ratio = picture.height / picture.width
    return Image(str(path), width=width, height=width*ratio)

def page_number(canvas, document):
    canvas.saveState()
    canvas.setFont("Arial", 9)
    canvas.setFillColor(colors.HexColor("#66758A"))
    canvas.drawRightString(7.72*inch, .44*inch, str(document.page))
    canvas.restoreState()

def main():
    document = Document(SOURCE)
    story = []
    images = [
        ROOT / "docs" / "diagramas" / "casos-de-uso.png",
        ROOT / "docs" / "diagramas" / "classes-projeto.png",
    ]
    image_index = 0
    for block in document.iter_inner_content():
        if isinstance(block, DocxTable):
            rows = []
            for i, row in enumerate(block.rows):
                style = styles["CellHeadCustom"] if i == 0 else styles["CellCustom"]
                rows.append([Paragraph(escape(cell.text), style) for cell in row.cells])
            t = Table(rows, colWidths=[.72*inch, 6.08*inch], repeatRows=1, hAlign="CENTER")
            t.setStyle(TableStyle([
                ("BACKGROUND", (0,0),(-1,0),colors.HexColor("#213E63")),
                ("ROWBACKGROUNDS",(0,1),(-1,-1),[colors.white,colors.HexColor("#F3F6FA")]),
                ("GRID",(0,0),(-1,-1),.35,colors.HexColor("#D9D9D9")),
                ("VALIGN",(0,0),(-1,-1),"MIDDLE"),
                ("LEFTPADDING",(0,0),(-1,-1),9),("RIGHTPADDING",(0,0),(-1,-1),9),
                ("TOPPADDING",(0,0),(-1,-1),6),("BOTTOMPADDING",(0,0),(-1,-1),6),
            ]))
            story.extend([t, Spacer(1,8)])
        elif isinstance(block, DocxParagraph):
            xml = block._p.xml
            if 'w:type="page"' in xml:
                story.append(PageBreak())
            elif "w:drawing" in xml:
                if image_index < len(images):
                    story.append(diagram(images[image_index]))
                    image_index += 1
            elif block.text.strip():
                name = block.style.name
                style = styles["TitleCustom"] if name=="Title" else styles["Head1Custom"] if name=="Heading 1" else styles["Head2Custom"] if name=="Heading 2" else styles["BodyCustom"]
                body = "".join(
                    f'<font name="ArialBold">{escape(run.text)}</font>' if run.bold else escape(run.text)
                    for run in block.runs
                )
                story.append(Paragraph(body or escape(block.text), style))
    pdf = SimpleDocTemplate(str(OUTPUT), pagesize=(8.5*inch,11*inch),
        leftMargin=.78*inch,rightMargin=.78*inch,topMargin=.7*inch,bottomMargin=.68*inch,
        author="Felipe Melo dos Santos", title="Documentação de análise e projeto do Hoshi Calendar",
        subject="Trabalho prático de Análise e Projeto de Sistemas")
    pdf.build(story,onFirstPage=page_number,onLaterPages=page_number)
    print(OUTPUT)

if __name__ == "__main__":
    main()
