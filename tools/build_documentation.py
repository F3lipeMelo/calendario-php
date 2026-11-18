from pathlib import Path
from math import hypot
from PIL import Image, ImageDraw, ImageFont
from docx import Document
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_CELL_VERTICAL_ALIGNMENT
from docx.oxml import OxmlElement
from docx.oxml.ns import qn

ROOT = Path(__file__).resolve().parents[1]
DOCS = ROOT / "docs"
DIAGRAMS = DOCS / "diagramas"
DIAGRAMS.mkdir(parents=True, exist_ok=True)
FONT = "C:/Windows/Fonts/arial.ttf"
FONT_BOLD = "C:/Windows/Fonts/arialbd.ttf"
INK, BLUE, PALE, GRAY = "#1D2635", "#213E63", "#E9F0F8", "#66758A"

def font(size, bold=False):
    return ImageFont.truetype(FONT_BOLD if bold else FONT, size)

def text(draw, xy, value, size=22, color=INK, bold=False, anchor=None):
    draw.text(xy, value, font=font(size, bold), fill=color, anchor=anchor)

def actor(draw, x, y, label):
    draw.ellipse((x-18,y-83,x+18,y-47), outline=BLUE, width=4)
    draw.line((x,y-47,x,y+28), fill=BLUE, width=4)
    draw.line((x-43,y-20,x+43,y-20), fill=BLUE, width=4)
    draw.line((x,y+28,x-35,y+81), fill=BLUE, width=4)
    draw.line((x,y+28,x+35,y+81), fill=BLUE, width=4)
    text(draw,(x,y+100),label,26,anchor="mm")

def ellipse(draw, box, label, planned=False):
    draw.ellipse(box, outline=GRAY if planned else BLUE, width=3, fill="#FFFFFF")
    cx=(box[0]+box[2])//2; cy=(box[1]+box[3])//2
    text(draw,(cx,cy),label,26,GRAY if planned else INK,bold=True,anchor="mm")

def include_arrow(draw, start, end):
    x1, y1 = start; x2, y2 = end
    length = hypot(x2 - x1, y2 - y1)
    ux, uy = (x2 - x1) / length, (y2 - y1) / length
    distance = 0
    while distance < length - 18:
        stop = min(distance + 12, length - 18)
        draw.line((x1 + ux*distance, y1 + uy*distance,
                   x1 + ux*stop, y1 + uy*stop), fill=GRAY, width=3)
        distance += 21
    draw.line((x2 - ux*18, y2 - uy*18, x2, y2), fill=GRAY, width=3)
    draw.line((x2 - ux*16 - uy*8, y2 - uy*16 + ux*8, x2, y2), fill=GRAY, width=3)
    draw.line((x2 - ux*16 + uy*8, y2 - uy*16 - ux*8, x2, y2), fill=GRAY, width=3)

def use_case_diagram():
    im=Image.new("RGB",(1500,1000),"white"); d=ImageDraw.Draw(im)
    d.rounded_rectangle((270,50,1240,930),radius=18,outline=BLUE,width=4)
    text(d,(755,75),"Hoshi Calendar",30,BLUE,True,anchor="mt")
    actor(d,130,425,"Usuário")
    actor(d,1370,625,"Serviço externo")
    boxes=[
        (350,170,705,255,"Criar marcação",False),
        (350,305,705,390,"Consultar calendário",False),
        (350,440,705,525,"Editar marcação",False),
        (350,575,705,660,"Concluir ou reabrir",False),
        (350,710,705,795,"Excluir marcação",False),
        (790,250,1150,335,"Validar período",False),
        (790,575,1150,660,"Enviar lembrete",True),
    ]
    for x1,y1,x2,y2,label,planned in boxes: ellipse(d,(x1,y1,x2,y2),label,planned)
    for yy in [212,347,482,617,752]: d.line((168,425,350,yy),fill=BLUE,width=3)
    include_arrow(d,(705,212),(790,292))
    include_arrow(d,(705,482),(790,292))
    text(d,(711,180),"<<include>>",22,GRAY)
    text(d,(711,390),"<<include>>",22,GRAY)
    d.line((1150,617,1330,625),fill=GRAY,width=3)
    text(d,(805,735),"Cinza: funcionalidade planejada",24,GRAY)
    path=DIAGRAMS/"casos-de-uso.png"; im.save(path); return path

def class_box(draw, box, name, attrs, methods, planned=False, stereotype=None):
    x1, y1, x2, y2 = box
    draw.rounded_rectangle(box, radius=10, outline=GRAY if planned else BLUE, width=4, fill="white")
    draw.rectangle((x1+3, y1+3, x2-3, y1+70), fill=PALE)
    draw.line((x1, y1+70, x2, y1+70), fill=BLUE, width=3)
    if stereotype:
        text(draw, ((x1+x2)//2, y1+21), f"«{stereotype}»", 22, GRAY, anchor="mm")
    text(draw, ((x1+x2)//2, y1+47 if stereotype else y1+36), name, 31, BLUE, True, anchor="mm")

    def row(value, y):
        size = 27
        while draw.textlength(value, font=font(size)) > x2-x1-34 and size > 21:
            size -= 1
        if draw.textlength(value, font=font(size)) > x2-x1-34:
            raise ValueError(f"Texto excede a classe {name}: {value}")
        text(draw, (x1+17, y), value, size)

    y = y1+91
    for value in attrs:
        row(value, y); y += 36
    if methods:
        draw.line((x1, y+4, x2, y+4), fill=BLUE, width=3)
        y += 22
        for value in methods:
            row(value, y); y += 36
    if planned:
        text(draw, (x2-16, y2-17), "planejada", 22, GRAY, anchor="rs")
    if y > y2-20:
        raise ValueError(f"Conteúdo excede a altura da classe {name}")

def composition_diamond(draw, start, end, color=BLUE):
    x1, y1 = start; x2, y2 = end
    length = hypot(x2-x1, y2-y1)
    ux, uy = (x2-x1)/length, (y2-y1)/length
    px, py = -uy, ux
    base_x, base_y = x1+ux*6, y1+uy*6
    draw.polygon([
        (base_x, base_y),
        (base_x+ux*19+px*14, base_y+uy*19+py*14),
        (base_x+ux*39, base_y+uy*39),
        (base_x+ux*19-px*14, base_y+uy*19-py*14),
    ], outline=color, fill=color)

def class_diagram():
    im = Image.new("RGB", (1800, 1560), "white"); d = ImageDraw.Draw(im)
    class_box(d, (60, 60, 660, 350), "Usuario",
              ["- id: int", "- nome: String"],
              ["+ abrirCalendario(): Calendario", "+ criar(titulo: String): Marcacao"], True)
    class_box(d, (800, 60, 1710, 350), "Calendario",
              ["- id: int", "- nome: String"],
              ["+ listar(inicio: Date, fim: Date): List<Marcacao>",
               "+ marcacoesEm(data: Date): List<Marcacao>"], True)
    class_box(d, (60, 480, 660, 940), "TipoMarcacao",
              ["TAREFA", "COMPROMISSO", "PRAZO", "LEMBRETE", "EVENTO", "OUTRO"],
              [], stereotype="enumeration")
    class_box(d, (800, 470, 1710, 1010), "Marcacao",
              ["- id: int", "- titulo: String", "- descricao: String", "- inicio: Date",
               "- fim: Date?", "- hora: Time", "- tipo: TipoMarcacao", "- concluida: Boolean"],
              ["+ validarPeriodo(): Boolean", "+ concluir(): void", "+ reabrir(): void"])
    class_box(d, (60, 1130, 700, 1450), "Notificacao",
              ["- id: int", "- antecedenciaMin: int", "- enviadaEm: DateTime?"],
              ["+ agendar(inicio: Date): void", "+ cancelar(): void"], True)
    class_box(d, (1040, 1130, 1710, 1450), "ServicoNotificacao",
              [], ["+ enviar(mensagem: String): Boolean"], True, "interface")

    d.line((660, 205, 800, 205), fill=BLUE, width=4)
    text(d, (675, 170), "1", 27); text(d, (775, 170), "1", 27)
    d.line((1255, 350, 1255, 470), fill=BLUE, width=4)
    composition_diamond(d, (1255, 350), (1255, 470))
    text(d, (1282, 370), "1", 27); text(d, (1282, 445), "0..*", 27)
    d.line((660, 705, 800, 705), fill=BLUE, width=4)
    text(d, (675, 672), "1", 27); text(d, (752, 672), "0..*", 27)
    d.line((800, 870, 700, 1280), fill=GRAY, width=4)
    composition_diamond(d, (800, 870), (700, 1280), GRAY)
    text(d, (755, 910), "1", 27); text(d, (697, 1095), "0..*", 27)
    d.line((700, 1290, 1040, 1290), fill=GRAY, width=4)
    text(d, (718, 1255), "0..*", 27); text(d, (1010, 1255), "1", 27)
    text(d, (60, 1510), "Cinza: componente planejado   •   Losango preenchido: composição", 24, GRAY)
    path = DIAGRAMS/"classes-projeto.png"; im.save(path); return path

def shade(cell, fill):
    tcPr=cell._tc.get_or_add_tcPr(); shd=OxmlElement("w:shd");shd.set(qn("w:fill"),fill);tcPr.append(shd)

def borders(cell):
    tcPr=cell._tc.get_or_add_tcPr(); b=OxmlElement("w:tcBorders")
    for side in ["top","left","bottom","right"]:
        x=OxmlElement("w:"+side);x.set(qn("w:val"),"single");x.set(qn("w:color"),"D9D9D9");x.set(qn("w:sz"),"4");b.append(x)
    tcPr.append(b)

def table(doc, heads, rows, widths):
    t=doc.add_table(rows=1,cols=len(heads));t.alignment=WD_TABLE_ALIGNMENT.CENTER
    for j,h in enumerate(heads):
        c=t.rows[0].cells[j];c.text=h;shade(c,"213E63");borders(c)
        for run in c.paragraphs[0].runs: run.font.color.rgb=RGBColor(255,255,255);run.bold=True
    for i,row in enumerate(rows):
        cells=t.add_row().cells
        for j,val in enumerate(row):
            cells[j].text=str(val);borders(cells[j]);cells[j].vertical_alignment=WD_CELL_VERTICAL_ALIGNMENT.CENTER
            if i%2: shade(cells[j],"F3F6FA")
    for row in t.rows:
        for j,c in enumerate(row.cells):
            c.width=Inches(widths[j])
            for p in c.paragraphs:
                p.paragraph_format.space_after=Pt(2)
                for run in p.runs: run.font.size=Pt(9)
    doc.add_paragraph()
    return t

def add_case(doc, code, title, actor, pre, main, alternate, exception, post):
    doc.add_heading(f"{code} {title}",level=2)
    for label,value in [("Ator",actor),("Pré-condições",pre),("Fluxo principal",main),("Fluxo alternativo",alternate),("Exceções",exception),("Pós-condições",post)]:
        p=doc.add_paragraph();p.add_run(label+": ").bold=True;p.add_run(value)

def build():
    use=use_case_diagram(); classes=class_diagram()
    doc=Document(); sec=doc.sections[0];sec.page_width=Inches(8.5);sec.page_height=Inches(11)
    doc.core_properties.author = "Felipe Melo dos Santos"
    doc.core_properties.last_modified_by = "Felipe Melo dos Santos"
    doc.core_properties.title = "Documentação de análise e projeto do Hoshi Calendar"
    doc.core_properties.subject = "Trabalho prático de Análise e Projeto de Sistemas"
    sec.top_margin=Inches(.72);sec.bottom_margin=Inches(.68);sec.left_margin=Inches(.78);sec.right_margin=Inches(.78)
    styles=doc.styles
    for name in ["Normal","Title","Heading 1","Heading 2"]:
        style=styles[name];style.font.name="Arial";style.font.color.rgb=RGBColor(0,0,0)
    styles["Normal"].font.size=Pt(10.5);styles["Normal"].paragraph_format.space_after=Pt(7)
    styles["Title"].font.size=Pt(25);styles["Heading 1"].font.size=Pt(15);styles["Heading 2"].font.size=Pt(12)
    styles["Heading 1"].paragraph_format.space_before=Pt(15);styles["Heading 1"].paragraph_format.space_after=Pt(7)
    styles["Heading 2"].paragraph_format.space_before=Pt(11);styles["Heading 2"].paragraph_format.space_after=Pt(5)
    p=doc.add_paragraph(style="Title");p.add_run("Documentação de análise e projeto do Hoshi Calendar")
    doc.add_paragraph("Trabalho prático de Análise e Projeto de Sistemas")
    doc.add_paragraph("Autor  Felipe Melo dos Santos")
    doc.add_paragraph("Data  18 de novembro de 2026")
    doc.add_paragraph("Repositório  https://github.com/F3lipeMelo/calendario-php")
    doc.add_paragraph("Neste documento apresento o escopo, os requisitos, os casos de uso e o modelo de classes do calendário que desenvolvi. A versão inicial permite gerenciar marcações de vários tipos e períodos; o envio automático de lembretes permanece previsto para uma etapa futura.")
    doc.add_page_break()
    doc.add_heading("1 Descrição do projeto",level=1)
    doc.add_paragraph("Desenvolvi o Hoshi Calendar para organizar tarefas, compromissos, prazos, lembretes e eventos em uma agenda única. O problema tratado é a perda de contexto quando atividades com duração de vários dias são cadastradas como vários compromissos separados.")
    doc.add_paragraph("Meu objetivo é registrar uma marcação uma única vez, com início e fim, e permitir sua consulta nas visões diária, semanal e mensal. A visão mensal mostra indicadores; as visões diária e semanal mostram mais detalhes. Posso editar, concluir, reabrir e excluir as marcações.")
    doc.add_paragraph("Nesta versão, implementei uma aplicação local em PHP e MySQL, sem contas individuais. Deixei fora do escopo atual a sincronização com outros calendários, o envio automático de notificações, a colaboração entre pessoas e um aplicativo móvel nativo. Modelei o serviço de notificações como extensão futura.")
    doc.add_heading("2 Requisitos",level=1)
    doc.add_heading("2.1 Requisitos funcionais",level=2)
    rf=[
        ("RF01","Cadastrar marcação com título, tipo, data inicial e horário."),
        ("RF02","Informar data final opcional para uma marcação que dure vários dias."),
        ("RF03","Listar marcações nas visões diária, semanal e mensal."),
        ("RF04","Exibir uma marcação de período em cada dia abrangido, mantendo um único registro."),
        ("RF05","Visualizar os detalhes das marcações de um dia em painel lateral."),
        ("RF06","Filtrar marcações pelo tipo."),
        ("RF07","Editar título, descrição, tipo, horário e período de uma marcação."),
        ("RF08","Concluir e reabrir uma marcação."),
        ("RF09","Excluir uma marcação após confirmação."),
        ("RF10","Iniciar o cadastro com a data selecionada preenchida."),
        ("RF11","Agendar e enviar lembretes por serviço externo, em evolução futura."),
    ]
    table(doc,["ID","Descrição"],rf,[.7,6.1])
    doc.add_heading("2.2 Requisitos não funcionais",level=2)
    rnf=[
        ("RNF01","Executar em PHP 8 ou superior com MySQL ou MariaDB."),
        ("RNF02","Adaptar o layout a telas de 320 px de largura sem perder ações essenciais."),
        ("RNF03","Manter todas as ações acessíveis por teclado e indicar o foco visivelmente."),
        ("RNF04","Reduzir animações quando o sistema operacional solicitar movimento reduzido."),
        ("RNF05","Validar entradas no servidor e usar consultas SQL parametrizadas."),
        ("RNF06","Proteger ações de escrita contra requisições sem token de sessão válido."),
    ]
    table(doc,["ID","Descrição verificável"],rnf,[.7,6.1])
    doc.add_heading("2.3 Regras de negócio",level=2)
    rn=[
        ("RN01","A data final, quando informada, deve ser igual ou posterior à inicial."),
        ("RN02","Uma marcação ocupa todos os dias do intervalo, inclusive início e fim."),
        ("RN03","Uma marcação de vários dias continua sendo um único registro no banco."),
        ("RN04","Os tipos permitidos são tarefa, compromisso, prazo, lembrete, evento e outro."),
        ("RN05","Marcações antigas sem data final valem somente no dia inicial."),
        ("RN06","A conclusão pode ser revertida por meio da ação Reabrir."),
        ("RN07","A exclusão é definitiva e exige confirmação na interface."),
    ]
    table(doc,["ID","Regra"],rn,[.7,6.1])
    doc.add_page_break()
    doc.add_heading("3 Diagrama de casos de uso",level=1)
    doc.add_paragraph("O usuário é o ator da versão implementada. O serviço externo de notificações representa a extensão planejada. A validação de período é incluída nos fluxos de criação e edição.")
    doc.add_picture(str(use),width=Inches(6.75))
    doc.add_paragraph("Figura 1  Casos de uso dentro da fronteira do Hoshi Calendar")
    add_case(doc,"UC01","Cadastrar marcação","Usuário","A aplicação e o banco estão disponíveis.","1. Abrir Nova marcação. 2. Informar título, tipo, data inicial, horário e, se necessário, data final. 3. Salvar. O sistema valida o período e grava um registro.","O usuário deixa a data final vazia; a marcação vale apenas no dia inicial.","Título vazio, data inválida, tipo desconhecido ou fim anterior ao início impedem o cadastro.","A marcação aparece nos dias abrangidos.")
    doc.add_page_break()
    add_case(doc,"UC02","Consultar calendário","Usuário","O calendário está aberto.","1. Escolher dia, semana ou mês. 2. Navegar pelo período. 3. Selecionar um dia para ver os detalhes. O sistema consulta marcações que cruzam o intervalo exibido.","O usuário filtra por tipo ou navega para hoje.","Falha de conexão impede a consulta e deve ser informada.","As marcações do período são apresentadas sem alterar dados.")
    add_case(doc,"UC03","Alterar status","Usuário","Existe uma marcação cadastrada.","1. Abrir os detalhes. 2. Selecionar Concluir. O sistema atualiza o status.","Se já estiver concluída, o usuário seleciona Reabrir.","ID inexistente ou sessão inválida impedem a atualização.","O estado novo aparece na agenda.")
    add_case(doc,"UC04","Excluir marcação","Usuário","Existe uma marcação cadastrada.","1. Abrir os detalhes. 2. Selecionar Excluir. 3. Confirmar. O sistema apaga o registro.","O usuário cancela a confirmação e nenhum dado é alterado.","ID inexistente ou sessão inválida impedem a exclusão.","A marcação deixa de aparecer em todo o período.")
    doc.add_page_break()
    doc.add_heading("4 Diagrama de classes de projeto",level=1)
    doc.add_paragraph("Modelei as classes de projeto para orientar a evolução. Nesta versão, implementei as operações por funções e páginas PHP e guardei as marcações na tabela compromissos. Usuario, Calendario, Notificacao e ServicoNotificacao são classes planejadas; Marcacao representa o registro atual, e a enumeração TipoMarcacao representa o catálogo de tipos definido em PHP.")
    doc.add_picture(str(classes),width=Inches(6.85))
    doc.add_paragraph("Figura 2  Classes, atributos, métodos, tipos, visibilidades e multiplicidades")
    doc.add_paragraph("Relacionamentos: Usuario 1 a 1 Calendario (associação); Calendario 1 a 0..* Marcacao (composição); TipoMarcacao 1 a 0..* Marcacao (associação: cada marcação tem um tipo, que pode classificar várias marcações); Marcacao 1 a 0..* Notificacao (composição planejada); Notificacao 0..* a 1 ServicoNotificacao (associação planejada). Os losangos preenchidos indicam composição.")
    doc.add_heading("5 Repositório",level=1)
    doc.add_paragraph("Código fonte e histórico de commits: https://github.com/F3lipeMelo/calendario-php")
    path=DOCS/"documentacao-hoshi-calendar.docx";doc.save(path);print(path)

if __name__=="__main__": build()
