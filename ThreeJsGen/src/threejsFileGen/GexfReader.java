package threejsFileGen;

import java.io.File;
import java.io.IOException;
import java.nio.file.Path;
import java.util.HashSet;
import java.util.Scanner;
import java.util.Set;
import java.util.regex.Matcher;
import java.util.regex.Pattern;

public class GexfReader {

	Set<String> dateStrings = new HashSet<String>();
	Set<Integer> dateInts = new HashSet<Integer>();
	
	public Layer createLayer(Path filePath, int fileCounter) {
		Layer layer;
		System.out.println("path: "+filePath.toString());
		
		String date = findDate(filePath.toString());
		System.out.println("date: "+date);
		if( !dateStrings.add(date) ) {
			System.out.println( "Duplicate Date:"+date );
			System.exit(0);
		}
		
		int intDate = convertDateToInt(0, date);
		System.out.println("Z: "+intDate);
		if( !dateInts.add(intDate) ) {
			System.out.println( "Duplicate Date:"+date+" int:"+intDate );
			System.exit(0);
		}
		
		layer = readFile(filePath.toString(), intDate, fileCounter);
		return layer;
	}

	public Layer readFile(String path, int dateIn, int fileCounter){
		Layer tempLayer = new Layer(dateIn);
		String token;
		try{
			Scanner file = new Scanner(new File(path));
			while (file.hasNext()){
				token = file.next();
				if (token.equalsIgnoreCase("<edge")){
					//System.out.println("edge");
					tempLayer.addEdge(loadEdge(file));
				}
				else if(token.equalsIgnoreCase("<node")){
					tempLayer = loadNodes(tempLayer, file, fileCounter);
				}
			}
		}
		catch(IOException e){
			System.err.println(e.getMessage());
		}
		return tempLayer;
	}
	
	private Layer loadNodes(Layer tempLayer, Scanner file, int fileCounter) throws IOException{
		String token = "";
		Node tempNode = new Node();
		
        do{
			token = file.next();
			if (token.startsWith("id=")){
				tempNode.setId(token.substring(4, findSecondQuote(4, token)));
			}
			else if (token.startsWith("label=")){
				tempNode.setLabel(token.substring(7, findSecondQuote(7, token)));
			}
			else if (token.equalsIgnoreCase("for=\"modularity_class\"")){
				token = file.next();
				//System.out.println("THIS HERE: "+(fileCounter + Integer.parseInt(token.substring(7, findSecondQuote(7, token)))));
				tempNode.setModClass((fileCounter + Integer.parseInt(token.substring(7, findSecondQuote(7, token)))));
			}
			else if (token.equalsIgnoreCase("<viz:size")){
				token = file.next();
				tempNode.setSize(Double.parseDouble(token.substring(7, findSecondQuote(7, token))));
			}
			else if (token.startsWith("start=")){
				tempNode.setZ(convertDateToInt(7, token));
			}
			else if (token.startsWith("x=")){
				tempNode.setX(Double.parseDouble(token.substring(3, findSecondQuote(3, token))));
			}
			else if (token.startsWith("y=")){
				tempNode.setY(Double.parseDouble(token.substring(3, findSecondQuote(3, token))));
			}
			
			else if (token.equalsIgnoreCase("<viz:color")){
				int r, g, b;
				token = file.next();
				r = Integer.parseInt(token.substring(3, findSecondQuote(3, token)));
				token = file.next();
				g = Integer.parseInt(token.substring(3, findSecondQuote(3, token)));
				token = file.next();
				b = Integer.parseInt(token.substring(3, findSecondQuote(3, token)));
				tempNode.setColor(new NodeColor(String.format("#%02X%02X%02X", r, g, b)));
			}
		}while(!token.equalsIgnoreCase("</node>"));
        if(tempNode.getZ() == 0){
        	tempNode.setZ(tempLayer.getDate());
        }
        if(tempLayer.checkModClassExist(tempNode.getModClass())){
        	tempLayer.getCommunity(tempNode.getModClass()).addNode(tempNode);
        }
        else{
        	tempLayer.addCommunity(new Community(tempNode.getModClass()), tempNode.getModClass());
        	tempLayer.getCommunity(tempNode.getModClass()).addNode(tempNode);
        	tempLayer.getCommunity(tempNode.getModClass()).setColor(tempNode.getColor());
        }
        return tempLayer;
	}

	private Edge loadEdge(Scanner file) throws IOException{
		String token;
		Edge edge = new Edge();

		do{
			token = file.next();
			
			if(token.startsWith("source=")){
				edge.setSource(token.substring(8, findSecondQuote(8,token)));
			}
			else if(token.startsWith("target=")){
				edge.setTarget(token.substring(8, findSecondQuote(8,token)));
			}
			else if(token.startsWith("for=\"weight")){
				token = file.next();
				if(token.startsWith("value=\"")){
					edge.setWeight(Double.parseDouble(token.substring(7, findSecondQuote(7,token))));
				}
			}
			else if(token.startsWith("start=")){
				edge.setStart(convertDateToInt(7, token));
				if(token.endsWith(">")){
					edge.setEnd(convertDateToInt(7, token));
				}
			}
			else if(token.startsWith("endopen=")){
				edge.setEnd(convertDateToInt(9,token));
			}
		}while (!token.endsWith("</edge>"));
		return edge;
	}

	// Finds the second double quote
	private int findSecondQuote(int i, String s){
		int secondQuoteLocation = s.length();
		for(int j = i; j < s.length(); j++){
			if(s.charAt(j)== '"'){
				secondQuoteLocation = j;
			}
		}
		return secondQuoteLocation;
	}

	// Finds the date in a string with yyyy-mm-dd format
	public String findDate(String s){
		Pattern p = Pattern.compile("\\d{4}-\\d{2}-\\d{2}");
		Matcher m = p.matcher(s);
		if(m.find()){
			return m.group(0);
		}
		return "Date Not Found";
	}

	// Converts a yyyy-mm-dd format into integer format
	public int convertDateToInt(int firstQuotePosition, String token) {
		int date;
		String unparsedDate;
		int year;
		int month;
		int day;

		unparsedDate = token.substring(firstQuotePosition, findSecondQuote(firstQuotePosition, token));
		year = Integer.parseInt(unparsedDate.substring(0, 4));
		month = Integer.parseInt(unparsedDate.substring(5, 7));
		day = Integer.parseInt(unparsedDate.substring(8, 10));

		// Not adjusted for Leap year.
		year = year * 365;
		int daysInMonthsSoFar = 0;
		int[] daysInEachMonth = { 
				31, // January
				28, // February
				31, // March 
				30, // April
				31, // May
				30, // June
				31, // July
				31, // August
				30, // September
				31, // October
				30, // November
				31  // December
		};
		for( int i=0; i<month-1; i++ ) { 
			// if month=1, we don't want daysInMonthsSoFar=0
			// and if month=12, we only want up to November added in
			// i.e. we never actually use December
			daysInMonthsSoFar += daysInEachMonth[i];
		}
		
		date = day + daysInMonthsSoFar + year;
		return date;
	}
}
