package punchmania.punchmania;

import android.os.Bundle;
import android.support.v7.app.AppCompatActivity;
import android.widget.ArrayAdapter;
import android.widget.ListAdapter;
import android.widget.ListView;
import java.util.ArrayList;

/**
 * Class is receiving the FastPunch highscorelist from server.
 *
 * @author Anna Brondin
 * @author Petar Novkovic
 */
public class HighScoreFast extends AppCompatActivity {

    private static final String TAG = "HighScore FastMode";
    private ListView listViewFast;
    private updater updater = new updater();
    private ArrayList<String> convertedHighScoreListOld = new ArrayList<>();

    /**
     * Main method starts the other methods.
     * @param savedInstanceState
     */
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_highscore_fast);
        listViewFast = (ListView) findViewById(R.id.highScoreListViewFast);

        updater.start();
    }

    /**
     * Creates an array list and copies the content from the receiving list from server.
     * This array list is placed in a list adapter and shown in UI (activity_highscore_fast).
     */
    private void populateListView() {
        ArrayList<String> convertedHighScoreList = new ArrayList<>();
        for (int i = 0; i < MainActivity.getHighScoresFast().size(); i++) {
            convertedHighScoreList.add(i + 1 + ":   " + MainActivity.getHighScoresFast().getUser(i).getUser() + "    " + MainActivity.getHighScoresFast().getUser(i).getScore());
        }
        if (!convertedHighScoreListOld.toString().equals(convertedHighScoreList.toString())) {
            convertedHighScoreListOld = convertedHighScoreList;
            ListAdapter adapter = new ArrayAdapter<>(this, android.R.layout.simple_list_item_1, convertedHighScoreList);
            listViewFast.setAdapter(adapter);
        }
    }

    /**
     * Inner class running the method populateListView() every second to se if the list should be updated in UI.
     */
    public class updater extends Thread {
        @Override
        public void run() {
            while (!isInterrupted()) {
                try {
                    synchronized (this) {
                        wait(1000);
                        if (!isInterrupted())
                            runOnUiThread(new Runnable() {
                                public void run() {
                                    populateListView();
                                }
                            });
                    }
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
        }
    }
}
