package server;

import java.awt.Dimension;
import java.io.DataInputStream;
import java.io.DataOutputStream;
import java.io.IOException;
import java.io.ObjectInputStream;
import java.io.ObjectOutputStream;
import java.net.ServerSocket;
import java.net.Socket;
import java.util.ArrayList;
import java.util.Timer;
import java.util.TimerTask;
import javax.swing.JFrame;
import common.HighScoreList;
import common.Message;
import common.Queue;
import server.Server.Client.ClientHandler;
/**
 * Server for managing clients and punchingbags
 *@author Sebastian Carlsson
 *@author Marcus Nordström
 *@author Jake O�Donnell
 *@author Benjamin Jonsson Zakrisson
 */
public class Server {
	/*
	 * final instructions for both receive and send methods
	 * 
	 */
	public static final int IS_HIGHSCORE = 3;
	public static final int QUEUE = 4;
	public static final int HIGHSCORE = 5;
	public static final int TOP_HIGHSCORE = 6;
	public static final int SEND_QUEUE = 8;
	public static final int SEND_HARDPUNCH_HIGHSCORE = 7;
	public static final int SEND_FASTPUNCH_HIGHSCORE = 9;

	public static final String FASTPUNCH_MODE = "FAST";
	public static final String HARDPUNCH_MODE = "HARD";

	private Calculator cal = new Calculator(this);
	private ArrayList<ClientHandler> clientList = new ArrayList<ClientHandler>();
	private Timer timer = new Timer();
	private ServerSocket serverSocketIs;
	private ServerSocket serverSocketClient;
	private HighScoreList hsList;
	private Client client;
	private Queue queue;
	private IS is;
	private ServerUI ui;
	public MySql ms;
	private long lastCheckSum;
	/**
	 *  Server constructor
	 * @param portIs the bindport for IS
	 * @param portClient the bind port for clients
	 * @param serverui reference to the UI
	 */
	public Server(int portIs, int portClient, ServerUI serverui) {
		try {
			serverSocketIs = new ServerSocket(portIs);
			serverSocketClient = new ServerSocket(portClient);
		} catch (IOException e) {
			e.printStackTrace();
		}
		this.ui = serverui;
		ui.addManager(this);
		queue = new Queue();
		client = new Client(serverSocketClient);
		is = new IS(serverSocketIs);
		ms = new MySql();
		ui.print("SQL connected",0);
		start();
	}
	/**
	 * Send a byte of data to the IS
	 * @param send
	 */
	public void isSendByte(byte send) {
		is.sendByte(send);
	}
	
	/**
	 * A task running on a separate clock that gets updates form SQL and breadcasts to connected clients
	 */
	TimerTask task = new TimerTask() {
		public void run() {
			if(lastCheckSum != ms.getCheckSum()) {
				lastCheckSum = ms.getCheckSum();
				sendQueue();
				sendHardPunchHighscore();
				sendFastPunchHighscore();
			}
		}
	};
	/**
	 * Set which mode the server is running
	 * @param i
	 */
	public void setSend(int i) {
		switch(i) {
		case IS_HIGHSCORE:
			isSendByte((byte)3);
			break;
		case QUEUE:
			client.clientMethods(SEND_QUEUE);
			break;
		case SEND_HARDPUNCH_HIGHSCORE:
			client.clientMethods(SEND_HARDPUNCH_HIGHSCORE);
			break;
		case SEND_FASTPUNCH_HIGHSCORE:
			client.clientMethods(SEND_FASTPUNCH_HIGHSCORE);
		}
	}
	/**
	 * Start of thread
	 */
	public void start() {
		lastCheckSum = ms.getCheckSum();
		timer.scheduleAtFixedRate(task, 500, 500);
	}
	/**
	 * Send queue to connected clients
	 */
	public void sendQueue() {
		setSend(QUEUE);
	}
	/**
	 * Send the highscore list for HardPunch to connected clients
	 */
	public void sendHardPunchHighscore() {
		setSend(SEND_HARDPUNCH_HIGHSCORE);
	}
	/**
	 * Send the highscore list for HardPunch to connected clients
	 */
	public void sendFastPunchHighscore() {
		setSend(SEND_FASTPUNCH_HIGHSCORE);
	}
	/**
	 * Tell connected "hosts" that a new highscore has been achived
	 * @param score
	 */
	public void newHs(int score) {
		if(score > ms.getTop1()) {
			setSend(IS_HIGHSCORE);
			client.clientMethods(TOP_HIGHSCORE);
		}
	}
	
	public void newFastHs(int score) {
		if(score > ms.getTop1Fast()) {
			setSend(IS_HIGHSCORE);
			client.clientMethods(TOP_HIGHSCORE);
			System.out.println("NEW FAST HS");
		}
	}
	/**
	 * Set the score for the next player in line
	 * @param score
	 * @param x
	 * @param y
	 * @param z
	 */
	public void setScore(int score, String x, String y, String z) {
		newHs(score);
		ms.setMySql(ms.popQueue(), score, x, y, z);
		client.clientMethods(SEND_HARDPUNCH_HIGHSCORE);
	}
	/**
	 * Send queue to connected clients
	 */
	public void broadcastQueue() {
		for(ClientHandler sendq : clientList) {
			sendq.sendQueue();
		}
	}
	/**
	 * Add new user to queue
	 * @param name Username
	 */
	public void addQueue(String name) {
		queue.add(name);
		ms.toQueue(name);
	}
	/**
	 * get the current queue
	 * @return	current queue
	 */
	public Queue getQueue() {
		return queue;
	}
	/**
	 * Sub-class for client connections
	 *
	 */
	public class Client {
		private ServerSocket socket;

		/*
		 * Creates the streams for values recived from IS, and stream for sending
		 * values.
		 */
		public Client(ServerSocket serverSocketClient) {
			this.socket = serverSocketClient;
			new ConnectionClient().start();
		}
		/**
		 * switch case for wich method to use
		 * @param i Method selector
		 */
		public void clientMethods(int i) {
			switch (i) {
			case TOP_HIGHSCORE: 
				for (ClientHandler sendTop : clientList) {
					sendTop.topHighscore();
				}	
				break;

			case SEND_HARDPUNCH_HIGHSCORE: 
				for (ClientHandler sendHardPunchHighscore : clientList) {
					sendHardPunchHighscore.sendHardPunchHighscore();
					broadcastQueue();
				}
				break;
			case SEND_QUEUE: 
				for (ClientHandler sendQueue : clientList) {
					sendQueue.sendQueue();
				}
				break;
			case SEND_FASTPUNCH_HIGHSCORE:
				for(ClientHandler sendFastPunchHighscore : clientList) {
					sendFastPunchHighscore.sendFastPunchHighScore();
					broadcastQueue();
				}
			}
		}
		/**
		 * Sub-class for client management
		 *
		 */
		public class ClientHandler extends Thread {
			@SuppressWarnings("unused")
			private ClientHandler clientHandler = this;
			private Socket socket;
			private ObjectOutputStream oos;
			private ObjectInputStream ois;
			/**
			 * constructor
			 * @param socketClient
			 */
			public ClientHandler(Socket socketClient) {
				this.socket = socketClient;
				try {
					oos = new ObjectOutputStream(socket.getOutputStream());
					ois = new ObjectInputStream(socket.getInputStream());
					sendQueue();
				} catch (IOException e) {
					e.printStackTrace();
				}
				this.start();
			}

			/*
			 * Read message from Client and prints it, sends value to calculator.
			 */
			public synchronized void run() {
				boolean connected = true;
				if (queue.size() > 0) {
					sendQueue();
				}
				sendHardPunchHighscore();
				sendFastPunchHighScore();
				while (connected) {
					try {
						Message message = (Message) ois.readObject();
						System.out.println(message.getInstruction());
						switch (message.getInstruction()) {
						case Message.NEW_USER_TO_QUEUE:
							ui.print("new user to queue : " + message.getPayload(), 0);
							String newtoqueue = (String)message.getPayload();
							addQueue(newtoqueue);
							broadcastQueue();
							break;

						case Message.CLIENT_REQUEST_PLAYERSCORES_HARDPUNCH:
							ui.print("HardPunchScore requested for :" + message.getPayload(), 0);
							String name = (String)message.getPayload();
							sendNameScore(name);
							break;

						case Message.CLIENT_REQUEST_HSDETAILS: 
							ui.print("User requested XYZ values", 0); 
							HighScoreList hslNameScore = (HighScoreList)message.getPayload(); 
							sendXYZ(hslNameScore.getUser(0).getUser(), hslNameScore.getUser(0).getScore()); 
							break;

						case Message.GAMEMODE:
							ui.print("Game mode chosen :" + message.getPayload(), 0);
							String mode = (String)message.getPayload();
							if(mode.equals(FASTPUNCH_MODE)) {
								sound("FAST");
								isSendByte((byte)4);
								System.out.println("FASTPUNCH");
							} else if(mode.equals(HARDPUNCH_MODE)) {
								sound("HARD");
								isSendByte((byte)5);
								System.out.println("HARDPUNCH");
							} else {
								System.err.println("Error, no mode equals: " + mode);
							}
							break;
							
						case Message.CLIENT_REQUES_PLAYERSCORES_FASTPUNCH:
							ui.print("FastPunchScore requested for :" + message.getPayload(), 0);
							String nameFastPunch = (String)message.getPayload();
							sendNameScoreFastPunch(nameFastPunch);
							break;
						} 

						
					} catch (IOException | ClassNotFoundException e) {
						try {
							ois.close();
							oos.close();
							socket.close();
							e.printStackTrace();
						} catch (IOException e2) {
							System.out.println("Stream close");
							e.printStackTrace();
						}
						connected = false;
						ui.print("CLIENT DISCONNECTED", 0);
						clientList.remove(this);
						this.interrupt();
					}
				}
			}
			/**
			 * Send the score belonging to a specific user back to client requesting
			 * @param name
			 */
			public void sendNameScore(String name) {
				try {
					hsList = ms.getUserScore(name);
					oos.writeObject(new Message(hsList, Message.SERVER_SEND_PLAYERSCORES_HARDPUNCH));
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			/**
			 * Send the score belonging to a specific user back to client requesting
			 * @param name
			 */
			public void sendNameScoreFastPunch(String name) {
				try {
					hsList = ms.getFastPunch(name);
					System.out.println(name);
					oos.writeObject(new Message(hsList, Message.SERVER_SEND_PLAYERSCORES_FASTPUNCH));
					for(int i= 0; i < hsList.size(); i++) {
					System.out.println(hsList.getUser(i).getUser());
					}
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			
			public void sound(String mode) {
				try {
					oos.writeObject(new Message(mode, Message.GAMEMODE));
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			/**
			 * Send the current highscorelist to all connected clients
			 */
			public void sendHardPunchHighscore() {
				try {
					sound("DONE");
					hsList = ms.getAllScore();
					oos.writeObject(new Message(hsList, Message.NEW_HIGHSCORELIST_HARDPUNCH));
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			/**
			 * Send the current fastpunchlist to all connected clients
			 */
			public void sendFastPunchHighScore() {
				try {
					sound("DONE");
					hsList = ms.getAllScoreFastPunch();
					oos.writeObject(new Message(hsList, Message.NEW_HIGHSCORELIST_FASTPUNCH));
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			/**
			 * send the current queue to all connected clients
			 */
			public void sendQueue() {
				try {
					queue = ms.getQueue();
					oos.writeObject(new Message(queue, Message.NEW_QUEUE));
					oos.reset();
					oos.flush();
				} catch (IOException e) {
					e.printStackTrace();
					System.out.println("Request to send queue failed");
				}
			}
			/**
			 * Tell all clients that a new highscore has been achieved
			 */
			public void topHighscore() {
					sound("DONE");
					sound("TOP");

			}
			/**
			 * Send XYZ belonging to a specific punch
			 * @param name
			 * @param score
			 */
			public void sendXYZ(String name, int score) { 
				try { 
					oos.writeObject(new Message(ms.getXYZ(name, score), Message.SERVER_SEND_HSDETAILS)); 
					oos.reset(); 
					oos.flush();
				} catch (IOException e) { 
					e.printStackTrace(); 
				} 
			}
		}
		/**
		 * Sub-class for the client connection listener
		 *
		 */
		private class ConnectionClient extends Thread {
			/*
			 * Waiting for connection, if connection is made new clienthandler is created
			 * with socket recived as parameter. clienthandler run method is started.
			 */
			public void run() {
				while (true) {
					try {
						ui.print("Client-port open on: " + socket.getLocalPort(), 0);
						Socket socketClient = socket.accept();
						ui.print("Client connected", 0);
						clientList.add(new ClientHandler(socketClient));
					} catch (IOException e) {
						System.err.println(e);
					}
				}
			}
		}

	}
	/**
	 * Sub-class for the IS client
	 *
	 */
	public class IS extends Thread{

		private DataOutputStream dos;
		private DataInputStream dis;
		private ISHandler ish;
		private String mode = "";
		private String hard = "HARD";
		private String fast = "FAST";
		private boolean listening;
		private Socket isSocket; 
		/**
		 * constructor for IS client
		 * @param serverSocketIs
		 */
		public IS(ServerSocket serverSocketIs) {
			new ConnectionIs().start();
		}
		/**
		 * Try establishing a new connection to IS
		 */
		public void reconnect() { 
			new ConnectionIs().start(); 
		} 
		/**
		 * Create a new IS manager
		 * @param socket
		 */
		public void newHandler(Socket socket) {
			isSocket = socket; 
			ish = new ISHandler(socket);
			ish.run();
		}
		/**
		 * Send 1 byte of rawdata to IS
		 * @param send
		 * @return
		 */
		public boolean sendByte(byte send) {
			if (dos != null) {
				try {
					dos = new DataOutputStream(isSocket.getOutputStream());
					dos.writeByte(send);
					dos.flush();
					if(send == 5) {
						mode = hard;
						listening = true;
					}else if (send == 4){
						mode = fast;
						listening = true;
					}
					return true;
				} catch (IOException e) {
					reconnect();
					e.printStackTrace();
					return false;
				}
			}
			return false;
		}
		/**
		 * Sub-class manager for IS client
		 * @author Sebastian Carlsson
		 *
		 */
		public class ISHandler implements Runnable {
			private Socket socket;
			private Timer timer1 = new Timer();
			/**
			 * constructor for IS manager
			 * @param socket
			 */
			public ISHandler(Socket socket) {
				try {
					this.socket = socket;
					dis = new DataInputStream(socket.getInputStream());
					dos = new DataOutputStream(socket.getOutputStream());
				} catch (IOException e) {
					e.printStackTrace();
				}
			}
			/**
			 * Try connection on a clock
			 */
			TimerTask task1 = new TimerTask() {
				public void run() {
					reconnect();
				}
			};
			/**
			 * Start the timer
			 */
			public void start1() {
				timer1.scheduleAtFixedRate(task1, 0, 1000);
			}
			/**
			 * Read data from IS and respond accordingliy
			 */
			public void run() {
				System.out.println("run started");
				boolean connected = true;
				try {
					dis = new DataInputStream(socket.getInputStream());
					
				} catch (IOException e2) {
					e2.printStackTrace();
				}
				start1();
				while (connected) {
					try {
						Thread.sleep(10);
					} catch (InterruptedException e1) {
						e1.printStackTrace();
					}
					while(listening) {
						if(mode.equals("HARD")) {
							timer1.cancel();
							int size = 0;
							try {
								size = dis.available();
							} catch (IOException e1) {
								e1.printStackTrace();
							}
							if(size > 0) {

								byte[]string = new byte[size];
								try {
									dis.readFully(string);
									String str = new String(string);
									System.out.println(str);
									int values = cal.calculateScore(str);
									ui.print("New score: " + values, 0);
									dis.reset(); // TA BORT HÄR
									listening = false;
								} catch (IOException e) {
									reconnect();
									connected = false;
									e.printStackTrace();

								}
							}
						} else if(mode.equals("FAST")) {
							timer1.cancel();
							int size = 0;
							try {
								size = dis.available();
							} catch (IOException e1) {
								e1.printStackTrace();
							}
							if(size > 0) {
								byte[] hit = new byte[size];
								try {
									dis.readFully(hit);
									String str = new String(hit);
									System.out.println(str);
									int i = Integer.parseInt(str);
									ms.setFastPunch(ms.popQueue(), i);
									System.out.println(i);
									newFastHs(i);
									dis.reset(); // TA BORT HÄR
									listening = false;
								} catch (IOException e) {
									e.printStackTrace();
									reconnect(); 
									connected = false;
								}
							}
						}
					}
				}
			}
		}

		/**
		 * Sub-class for IS connection listener
		 *
		 */
		private class ConnectionIs extends Thread {

			/*
			 * Waiting for connection, if connection is made new clienthandler is created
			 * with socket recived as parameter. clienthandler run method is started.
			 */
			public void run() {
				while (true) {
					try {
						Socket socketIs = serverSocketIs.accept();
						ui.print("Embedded connected", 0);
						newHandler(socketIs);

					} catch (IOException e) {
						reconnect();
						e.printStackTrace();
					}
				}
			}
		}
	}
	/**
	 * Start the server
	 * @param args
	 */
	public static void main(String[] args) {
		JFrame frame = new JFrame();
		ServerUI serverui = new ServerUI();
		frame = new JFrame("PUNCH MANIA");
		frame.setResizable(false);
		frame.setPreferredSize(new Dimension(500, 600));
		frame.add(serverui);
		frame.pack();
		frame.setLocationRelativeTo(null);
		frame.setVisible(true);
		frame.setDefaultCloseOperation(JFrame.EXIT_ON_CLOSE);
		Server server = new Server(12345, 9192, serverui);
	}

}